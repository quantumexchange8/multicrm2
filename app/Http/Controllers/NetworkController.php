<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountType;
use App\Models\IbAccountType;
use App\Models\User;

class NetworkController extends Controller
{
    //

    public function network(Request $request)
    {
        

        $code = $request->code;
        $search = [];
        $users = [];
        $childrens = Auth::user()->getChildrenIds();


        foreach ($childrens as $children) {
            $users = User::where('id', $children);
        }
        // $user = Auth::user();
        // // dd($user);
        // if($code) {
        //     $users = $users->where('hierarchyList', 'like', '%-' . Auth::id() . '-%');
        // //    dd($users);
        //     if($code) {
        //         $users = $users->where('referral_id', '=', $code);
        //         // dd($users);
        //     }
        // }
        // else
        // {
        //     $users = $users->where('id', Auth::id());
        // }
        // $users = $users->with('downline')->get();

        $userArray = [];
        $users = User::get_member_tree_record($search);

        foreach ($users as $user) {
            $userArray[] = [
                'user' => $user,
                'downline' => $user->downline,
            ];
        }
        
        $userDownlineArray = [];
        
        foreach ($userArray as $userItem) {
            $userDownlineArray[] = $userItem['downline'];
        }
        
        return Inertia::render('GroupNetwork/NetworkTree', [
            'users' => User::get_member_tree_record($search),
            'user_downline' => $userDownlineArray,
        ]);
    }
}
