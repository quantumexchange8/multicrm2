<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountType;
use App\Models\IbAccountType;
use App\Models\User;

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

    public function getRebateAllocation(Request $request)
    {

        $user = Auth::user();

        $accountTypes = IbAccountType::where('user_id', $user->id)->with(['accountType'])->get();

        $accounts = IbAccountType::where('user_id', $user->id)->with(['ofUser', 'symbolGroups.symbolGroup', 'accountType'])->first();
        
        $childrens = $this->getDownlinesRecursive($accounts);


        // dd($userGroupSymbol);

        return Inertia::render('GroupNetwork/RebateAllocation', [
            'accounts' => $accounts,
            'childrens' => $childrens,
        ]);
    }

    private function getDownlinesRecursive($account)
    {
        
        $childrens = [];

        if ($account->downline) {
            foreach ($account->downline as $downline) {
                $children = [
                    'account' => $downline->load(['ofUser', 'accountType', 'symbolGroups.symbolGroup']),
                    'children' => $this->getDownlinesRecursive($downline),
                ];
    
                // If the child has downlines, include them in the $children array
                if (count($children['children']) > 0) {
                    $children['children'] = $this->getDownlinesRecursive($downline);
                }
    
                $childrens[] = $children;
            }
        }

        return $childrens;
    }

    public function updateRebateAllocation(Request $request)
    {
        dd($request->all());

        if ($request->ajax()) {
            
        }

        return Redirect::route('group_network.rebate_allocation')->with('toast', 'Successfully Updated Rebate Allocation');
    }
}
