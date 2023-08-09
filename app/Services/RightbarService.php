<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\TradingAccount;
use App\Models\TradingUser;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RightbarService
{
    public function getPaymentAccount()
    {
        $conn = (new CTraderService)->connectionStatus();

        $user = Auth::user();
        $paymentAccounts = TradingUser::where('user_id', $user->id)->whereNot('module', 'pamm')->get();

        if ($conn['code'] == 0) {
            try {
                (new CTraderService)->getUserInfo($user->tradingUsers);
            } catch (\Exception $e) {
                \Log::error('CTrader Down');
            }
        }

        return $paymentAccounts;
    }
}
