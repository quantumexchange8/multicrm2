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
    private function convertToNestedStructure($user, $level = 0)
    {
        $userData = [
            'parent' => $user,
            'level' => $level,
            'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
        ];

        $children = $user->downline;

        if ($children->count() > 0) {
            $userData['children'] = [];
            foreach ($children as $child) {
                $userData['children'][] = $this->convertToNestedStructure($child, $level + 1);
            }
        }

        return $userData;
    }

    public function network(Request $request)
    {
        $user = Auth::user();

        $users = $this->convertToNestedStructure($user);

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
