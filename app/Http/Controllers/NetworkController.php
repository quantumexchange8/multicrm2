<?php

namespace App\Http\Controllers;

use App\Models\IbAccountTypeSymbolGroupRate;
use App\Models\RebateAllocation;
use App\Models\RebateAllocationRate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountType;
use App\Models\IbAccountType;
use App\Models\User;
use function GuzzleHttp\Promise\all;

class NetworkController extends Controller
{
    private function convertToNestedStructure($user, $search = null, $level = 0)
    {
        $user->load('downline:id,first_name,email,total_group_deposit,total_group_withdrawal,upline_id,role');

        // Count the total_ib and total_client using collection methods
        $totalIB = $user->downline->where('role', 'ib')->count();
        $totalClient = $user->downline->where('role', 'member')->count();

        $userData = [
            'parent' => $user,
            'level' => $level,
            'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
            'total_ib' => $totalIB,
            'total_client' => $totalClient,
        ];

        $children = $user->downline;

        if ($search) {
            $children = $children->filter(function ($child) use ($search) {
                if (str_contains($child->first_name, $search) || str_contains($child->email, $search)) {
                    return true;
                }

                if ($child->downline->count() > 0) {
                    // Recursively search in deeper levels
                    $nestedResult = $this->convertToNestedStructure($child, $search, 1);
                    return !empty($nestedResult['children']);
                }

                return false;
            });
        }

        if ($children->count() > 0) {
            $userData['children'] = [];
            foreach ($children as $child) {
                $userData['children'][] = $this->convertToNestedStructure($child, $search, $level + 1);
            }
        }

        return $userData;
    }

    public function network(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');

        $users = $this->convertToNestedStructure($user, $search);

        return Inertia::render('GroupNetwork/NetworkTree', [
            'users' => $users,
        ]);
    }

    public function getRebateAllocation()
    {
        $user = Auth::user();
        $search = \Request::input('search');

        $ib = IbAccountType::where('user_id', $user->id)->with(['ofUser', 'symbolGroups.symbolGroup', 'accountType'])->first();

        $childrenIds = $ib->getIbChildrenIds();

        $query = IbAccountType::whereIn('id', $childrenIds)
            ->with(['ofUser', 'symbolGroups.symbolGroup', 'accountType']);

        if ($search) {
            $query->whereHas('ofUser', function ($userQuery) use ($search) {
                $userQuery->where('first_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $children = $query->get();

        $children->each(function ($child) {
            $profilePicUrl = $child->ofUser->getFirstMediaUrl('profile_photo');
            $child->profile_pic = $profilePicUrl;
        });

        return Inertia::render('GroupNetwork/RebateAllocation', [
            'ib' => $ib,
            'children' => $children,
            'filters' => \Request::only(['search'])
        ]);
    }

    public function updateRebateAllocation(Request $request)
    {
        $curIb = IbAccountType::find($request->user_id);
        $upline = IbAccountType::where('user_id', Auth::id())->first();
        $downline = $curIb->downline;
        $curIbRate = IbAccountTypeSymbolGroupRate::where('ib_account_type', $request->user_id)->get()->keyBy('symbol_group');

        foreach ($request->symbolGroupItems as $key => $amount) {
            $parent = IbAccountTypeSymbolGroupRate::with('symbolGroup')->where('ib_account_type', $upline->id)->where('symbol_group', $key)->first();
            if ($parent && $amount > $parent->amount) {
                throw ValidationException::withMessages([
                    'symbolGroupItems' => [$key => 'Invalid Amount for ' . $parent->symbolGroup->name],
                ]);
            }
        }

        foreach ($downline as $child) {
            foreach ($request->symbolGroupItems as $key => $amount) {
                $childRate = IbAccountTypeSymbolGroupRate::with('symbolGroup')->where('ib_account_type', $child->id)->where('symbol_group', $key)->first();
                if ($amount < $childRate->amount) {
                    return response()->json([
                        'invalidAmount' => 'Invalid Amount for ' . $childRate->symbolGroup->name
                    ]);
                }
            }
        }

        $rebateAllocation = RebateAllocation::create(['from' => $curIb->upline_id, 'to' => $request->user_id]);

        foreach ($request->symbolGroupItems as $key => $amount) {

            RebateAllocationRate::create([
                'allocation_id' => $rebateAllocation->id,
                'symbol_group' => $key,
                'old' => $curIbRate[$key]->amount,
                'new' => $amount
            ]);

            $curIbRate[$key]->update(['amount' => $amount]);

        }

        return redirect()->back()->with('toast', 'The rebate allocation has been saved!');
    }
}
