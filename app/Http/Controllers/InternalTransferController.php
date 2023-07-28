<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\TradingUser;
use App\Models\User;
use App\Services\ChangeTraderBalanceType;
use App\Services\CTraderService;
use App\Services\RunningNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InternalTransferController extends Controller
{
    protected function getFilteredPayments($category, $type)
    {
        return Payment::query()
            ->where('user_id', Auth::id())
            ->where('category', $category)
            ->where('type', $type)
            ->latest()
            ->get();
    }

    public function transaction()
    {
        $user = Auth::user();

        $payments = $this->getFilteredPayments('payment', 'Deposit');
        $withdrawals = $this->getFilteredPayments('payment', 'Withdrawal');
        $walletToAccounts = $this->getFilteredPayments('internal_transfer', 'WalletToAccount');
        $accountToWallets = $this->getFilteredPayments('internal_transfer', 'AccountToWallet');
        $accountToAccounts = $this->getFilteredPayments('internal_transfer', 'AccountToAccount');
        $rebateToAccounts = $this->getFilteredPayments('apply_rebate', '');

        if ($user->hasRole('ib')) {
            $tradingUsers = TradingUser::where('user_id', $user->id)->where('module', '!=', 'pamm')->get();
        } else {
            $tradingUsers = $user->tradingUsers; // or $trading_users = [];
        }

        return Inertia::render('Transaction/InternalTransfer', [
            'tradingUsers' => $tradingUsers,
            'payments' => $payments,
            'withdrawals' => $withdrawals,
            'walletToAccounts' => $walletToAccounts,
            'accountToWallets' => $accountToWallets,
            'accountToAccounts' => $accountToAccounts,
            'rebateToAccounts' => $rebateToAccounts,
        ]);
    }

    public function wallet_to_account(Request $request)
    {
        $conn = (new CTraderService)->connectionStatus();
        if ($conn['code'] != 0) {
            if ($conn['code'] == 10) {
                return response()->json(['success' => false, 'message' => 'No connection with cTrader Server']);
            }
            return response()->json(['success' => false, 'message' => $conn['message']]);
        }

        $request->validate([
            'account_no' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);
        //$user = Auth::user();
        $user_id = Auth::id();
        $user = User::find($user_id);
        $amount = floatval($request->amount);
        if ($user->cash_wallet < $amount) {
            throw ValidationException::withMessages(['amount' => trans('Insufficient balance')]);
        }

        $payment_id = RunningNumberService::getID('transaction');
        try {
            $trade = (new CTraderService)->createTrade($request->account_no, $request->amount, "Wallet To Account", ChangeTraderBalanceType::DEPOSIT);
        } catch (\Throwable $e) {
            if ($e->getMessage() == "Not found") {
                TradingUser::firstWhere('meta_login', $request->account_no)->update(['acc_status' => 'Inactive']);
            } else {
                Log::error($e->getMessage());
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
        $ticket = $trade->getTicket();
        Payment::create([
            'user_id' => $user_id,
            'payment_id' => $payment_id,
            'category' => 'internal_transfer',
            'type' => 'WalletToAccount',
            'to' => $request->account_no,
            'amount' => $amount,
            'ticket' => $ticket,
            'status' => 'Successful',
        ]);

        $user->cash_wallet -= $amount;
        $user->save();
        return redirect()->back()->with('toast', 'Successful Transfer Wallet To Account!');
    }

    public function account_to_wallet(Request $request)
    {
        $conn = (new CTraderService)->connectionStatus();
        if ($conn['code'] != 0) {
            if ($conn['code'] == 10) {
                return response()->json(['success' => false, 'message' => 'No connection with cTrader Server']);
            }
            return response()->json(['success' => false, 'message' => $conn['message']]);
        }

        $request->validate([
            'account_no' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();

        $tradingUser = TradingUser::firstWhere('meta_login', $request->account_no);
        (new CTraderService)->getUserInfo([$tradingUser]);
        $tradingUser = TradingUser::firstWhere('meta_login', $request->account_no);

        if ($tradingUser->balance < $request->amount) {
            throw ValidationException::withMessages(['amount' => trans('Insufficient balance')]);
        }

        $payment_id = RunningNumberService::getID('transaction');
        try {
            $trade = (new CTraderService)->createTrade($request->account_no, $request->amount, "Account To Wallet", ChangeTraderBalanceType::WITHDRAW);
        } catch (\Throwable $e) {
            if ($e->getMessage() == "Not found") {
                TradingUser::firstWhere('meta_login', $request->account_no)->update(['acc_status' => 'Inactive']);
            } else {
                Log::error($e->getMessage());
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        $ticket = $trade->getTicket();

        Payment::create([
            'user_id' => $user->id,
            'payment_id' => $payment_id,
            'category' => 'internal_transfer',
            'type' => 'AccountToWallet',
            'from' => $request->account_no,
            'amount' => $request->amount,
            'ticket' => $ticket,
            'status' => 'Successful',

        ]);
        $user->cash_wallet += $request->amount;
        $user->save();
        return redirect()->back()->with('toast', 'Successful Transfer Account To Wallet!');
    }

    public function account_to_account(Request $request)
    {
        $conn = (new CTraderService)->connectionStatus();
        if ($conn['code'] != 0) {
            if ($conn['code'] == 10) {
                return response()->json(['success' => false, 'message' => 'No connection with cTrader Server']);
            }
            return response()->json(['success' => false, 'message' => $conn['message']]);
        }

        $request->validate([
            'account_no_1' => 'required|string',
            'account_no_2' => 'required|string|different:account_no_1',
            'amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $tradingUser = TradingUser::firstWhere('meta_login', $request->account_no_1);
        (new CTraderService)->getUserInfo([$tradingUser]);
        $tradingUser = TradingUser::firstWhere('meta_login', $request->account_no_1);

        if ($tradingUser->balance < $request->amount) {
            throw ValidationException::withMessages(['amount' => trans('Insufficient balance')]);
        }

        $payment_id = RunningNumberService::getID('transaction');
        try {
            $trade_1 = (new CTraderService)->createTrade($request->account_no_1, $request->amount, "Account To Account", ChangeTraderBalanceType::WITHDRAW);
        } catch (\Throwable $e) {
            if ($e->getMessage() == "Not found") {
                TradingUser::firstWhere('meta_login', $request->account_no_1)->update(['acc_status' => 'Inactive']);
            } else {
                Log::error($e->getMessage());
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
        try {
            $trade_2 = (new CTraderService)->createTrade($request->account_no_2, $request->amount, "Account To Account", ChangeTraderBalanceType::DEPOSIT);
        } catch (\Throwable $e) {
            if ($e->getMessage() == "Not found") {
                TradingUser::firstWhere('meta_login', $request->account_no_2)->update(['acc_status' => 'Inactive']);
            } else {
                Log::error($e->getMessage());
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
        $ticket = $trade_1->getTicket() . ', ' . $trade_2->getTicket();

        Payment::create([
            'user_id' => $user->id,
            'payment_id' => $payment_id,
            'category' => 'internal_transfer',
            'type' => 'AccountToAccount',
            'from' => $request->account_no_1,
            'to' => $request->account_no_2,
            'amount' => $request->amount,
            'ticket' => $ticket,
            'status' => 'Successful',

        ]);
        return redirect()->back()->with('toast', 'Successfully Transfer Account to Account');
    }
}