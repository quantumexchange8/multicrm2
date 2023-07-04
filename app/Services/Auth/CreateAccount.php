<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\RunningNumberService;
use Illuminate\Support\Facades\DB;

class CreateAccount
{
    public function execute(CreateUserDto $data): User
    {
        return $this->storeNewUser($data);
    }

    public function storeNewUser(CreateUserDto $data): User
    {
        $user = new User();


        $user->first_name = $data->first_name;
        $user->middle_name = $data->middle_name;
        $user->last_name = $data->last_name;
        $user->chinese_name = $data->chinese_name;
        $user->email = $data->email;
        $user->phone = $data->phone;
        $user->createPassword('password', $data->password);
        $user->address_1 = $data->address_1;
        $user->address_2 = $data->address_2;
        $user->postcode = $data->postcode;
        $user->country = $data->country;
        $user->nationality = $data->nationality;
        $user->race = $data->race;
        $user->dob = $data->dob;
        //  $user->doc_identity = $data->doc_identity;
        // $user->doc_address = $data->doc_address;
        $user->referral = $data->referral;
        $hierarchyList = null;
        if ($data->referral) {
            $upline = User::firstWhere('ib_id', $data->referral);
            if ($upline) {
                $upline->increment('direct_client');
                $parent = $upline;
                while ($parent) {

                    $parent->increment('total_client');
                    $parent->save();
                    $parent = $parent->upline;
                };

                if (!$upline->hierarchyList) {
                    $hierarchyList = "-" . $upline->id . "-";
                } else {
                    $hierarchyList = $upline->hierarchyList . $upline->id . "-";
                }
                $user->hierarchyList = $hierarchyList;
                $user->upline_id = $upline->id;
            }
        }

        $user->register_ip = $data->register_ip;


        $user->cash_wallet_id = RunningNumberService::getID('cash_wallet');

        DB::transaction(function () use ($user) {
            $user->save();
        });
        $path_url = 'uploads/' . $user->id;


        $doc_identity_front = $data->doc_identity_front;
        if ($doc_identity_front) {
            $doc_identity_front_imageName = pathinfo($doc_identity_front->getClientOriginalName(), PATHINFO_FILENAME) . time() . '.' . $doc_identity_front->getClientOriginalExtension();
            $doc_identity_front->move($path_url, $doc_identity_front_imageName);
            $user->doc_identity_front  = $doc_identity_front_imageName;
        }

        $doc_identity_back = $data->doc_identity_back;
        if ($doc_identity_back) {
            $doc_identity_front_imageName = pathinfo($doc_identity_back->getClientOriginalName(), PATHINFO_FILENAME) . time() . '.' . $doc_identity_back->getClientOriginalExtension();
            $doc_identity_back->move($path_url, $doc_identity_front_imageName);
            $user->doc_identity_back  = $doc_identity_front_imageName;
        }

        $doc_address = $data->doc_address;
        if ($doc_address) {
            $doc_address_imageName = pathinfo($doc_address->getClientOriginalName(), PATHINFO_FILENAME) . time() . '.' . $doc_address->getClientOriginalExtension();
            $doc_address->move($path_url, $doc_address_imageName);
            $user->doc_address = $doc_address_imageName;
        }

        DB::transaction(function () use ($user) {
            $user->save();
        });

        return $user;
    }
}
