<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Models\SettingCryptoWallet;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function deposit_modal()
    {
        $cryptoWallets = SettingCryptoWallet::with('media')->get();

        return response()->json([
            'cryptoWallets' => $cryptoWallets
        ]);
    }

    public function deposit(DepositRequest $request)
    {
        dd($request->all());
    }
}
