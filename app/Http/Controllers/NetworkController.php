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


}
