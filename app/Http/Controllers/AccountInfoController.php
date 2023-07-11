<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\SettingLeverage;
use App\Models\TradingAccount;
use App\Services\CTraderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AccountInfoController extends Controller
{
    public function account_info()
    {
        $trading_accounts = TradingAccount::query()
            ->with('accountType')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
        $accountTypes = AccountType::where('id', 1)->first();

        return Inertia::render('AccountInfo/AccountInfo', [
            'tradingAccounts' => $trading_accounts,
            'accountTypes' => $accountTypes,
            'leverages' => SettingLeverage::all(),
        ]);
    }

    public function add_trading_account(Request $request)
    {
        $conn = (new CTraderService)->connectionStatus();
        if ($conn['code'] != 0) {
            if ($conn['code'] == 10) {
                return response()->json(['success' => false, 'message' => 'No connection with cTrader Server']);
            }
            return response()->json(['success' => false, 'message' => $conn['message']]);
        }

        $request->validate([
            'leverage' => 'required|numeric',
            'currency' => 'required',
            'group' => 'required',
            'additionalNotes' => 'nullable',
        ]);

        $user = Auth::user();

        /*
        $mainPassword = Str::random(8);
        $investorPassword = Str::random(8);
         Mail::to($user->email)->send(new NewMetaAccount($mainPassword, $investorPassword));
        return true; */

        $group = $request->group;
        $mainPassword = Str::random(8);
        $investorPassword = Str::random(8);
        // $group = 'real\ETIQECNElite_A';
        // $group = Group::where('value', $group)->first()->value('meta_group_name');
        $group = AccountType::with('metaGroup')->where('id', $group)->get()->value('metaGroup.meta_group_name');

        $remarks = $user->remark . ' ' . $request->additionalNotes;
        $ctAccount = (new CTraderService)->createUser($user,  $mainPassword, $investorPassword, $group, $request->leverage, $request->group, null, null, $remarks);
        //Mail::to($user->email)->send(new NewMetaAccount($ctAccount['login'], $mainPassword, $investorPassword));
        return back()->with('toast', 'Successfully Created Trading Account');
        // return true;
    }
}
