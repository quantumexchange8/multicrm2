<?php

namespace App\Http\Controllers;

use App\Http\Requests\InternalTransferRequest;
use App\Models\FundAdjustment;
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
    public function transaction()
    {
        $user = Auth::user();
        $conn = (new CTraderService)->connectionStatus();

        if ($conn['code'] == 0) {
            try {
                (new CTraderService)->getUserInfo($user->tradingUsers);
            } catch (\Exception $e) {
                \Log::error('CTrader Error');
            }
        }

        if ($user->hasRole('ib')) {
            $tradingUsers = TradingUser::where('user_id', $user->id)->where('module', '!=', 'pamm')->get();
        } else {
            $tradingUsers = $user->tradingUsers; // or $trading_users = [];
        }

        return Inertia::render('Transaction/InternalTransfer', [
            'tradingUsers' => $tradingUsers,
        ]);
    }

    public function getTransaction(Request $request): \Illuminate\Http\JsonResponse
    {
        $paymentTypes = [
            'Deposit' => 'payment',
            'Withdrawal' => 'payment',
            'WalletToAccount' => 'internal_transfer',
            'AccountToWallet' => 'internal_transfer',
            'AccountToAccount' => 'internal_transfer',
            'RebateToWallet' => 'rebate_payout'
        ];

        $filteredPayments = [];

        foreach ($paymentTypes as $type => $category) {
            $payments = Payment::query()
                ->where('user_id', Auth::id())
                ->where('category', $category)
                ->where('type', $type)
                ->when($request->filled('date'), function ($query) use ($request) {
                    $date = $request->input('date');
                    $start_date = Carbon::createFromFormat('Y-m-d', $date[0])->startOfDay();
                    $end_date = Carbon::createFromFormat('Y-m-d', $date[1])->endOfDay();
                    $query->whereBetween('created_at', [$start_date, $end_date]);
                })
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = $request->input('search');
                    $query->where('to', 'like', '%' . $search . '%')
                        ->orWhere('from', 'like', '%' . $search . '%');
                })
                ->latest()
                ->paginate(10);

            $filteredPayments[$type] = $payments;
        }

        return response()->json($filteredPayments);
    }

    public function wallet_to_account(InternalTransferRequest $request)
    {
        $conn = (new CTraderService)->connectionStatus();
        if ($conn['code'] != 0) {
            if ($conn['code'] == 10) {
                return response()->json(['success' => false, 'message' => 'No connection with cTrader Server']);
            }
            return response()->json(['success' => false, 'message' => $conn['message']]);
        }

        //$user = Auth::user();
        $user_id = Auth::id();
        $user = User::find($user_id);
        $amount = floatval($request->amount);
        if ($user->cash_wallet < $amount) {
            throw ValidationException::withMessages(['amount' => trans('public.Insufficient balance')]);
        }

        $payment_id = RunningNumberService::getID('transaction');
        try {
            $trade = (new CTraderService)->createTrade($request->account_no, $request->amount, "QCG Wallet To Trading Account", ChangeTraderBalanceType::DEPOSIT);
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
        return redirect()->back()->with('toast', trans('public.Successful Transfer QCG Wallet To Trading Account!'));
    }

    public function account_to_wallet(InternalTransferRequest $request)
    {
        $conn = (new CTraderService)->connectionStatus();
        if ($conn['code'] != 0) {
            if ($conn['code'] == 10) {
                return response()->json(['success' => false, 'message' => 'No connection with cTrader Server']);
            }
            return response()->json(['success' => false, 'message' => $conn['message']]);
        }

        $user = Auth::user();
        $accountNo = $request->account_no;
        $amount = $request->amount;
        $comment = $user->first_name . ' transfer fund to QCG Wallet';

        $tradingUser = TradingUser::firstWhere('meta_login', $request->account_no);
        (new CTraderService)->getUserInfo([$tradingUser]);
        $tradingUser = TradingUser::firstWhere('meta_login', $request->account_no);

        if (($tradingUser->balance - $tradingUser->credit) < $request->amount) {
            throw ValidationException::withMessages(['amount' => trans('public.Insufficient balance')]);
        }

        $payment_id = RunningNumberService::getID('transaction');
        try {
            $bonus = (new CTraderService)->changeTraderBonus($accountNo, $tradingUser->bonus, $comment, ChangeTraderBalanceType::WITHDRAW);
            $credit = (new CTraderService)->createTrade($accountNo, $tradingUser->credit, $comment, ChangeTraderBalanceType::WITHDRAW_NONWITHDRAWABLE_BONUS);
            $trade = (new CTraderService)->createTrade($accountNo, $amount, "Trading Account To QCG Wallet", ChangeTraderBalanceType::WITHDRAW);
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

        $commonAdjustmentData = [
            'user_id' => $user->id,
            'to' => $accountNo,
            'comment' => $comment,
            'internal_description' => $comment,
            'client_description' => $comment,
            'allotted_time' => 0,
            'start_date' => Carbon::now()->format('Y-m-d'),
            'expiry_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'completed',
            'handle_by' => Auth::id(),
        ];

        // Create FundAdjustment records for bonus and credit
        FundAdjustment::create(array_merge($commonAdjustmentData, ['type' => 'bonus_out', 'amount' => $tradingUser->bonus, 'ticket' => $bonus->getTicket()]));
        FundAdjustment::create(array_merge($commonAdjustmentData, ['type' => 'credit_out', 'amount' => $tradingUser->credit, 'ticket' => $credit->getTicket()]));

        $user->cash_wallet += $request->amount;
        $user->save();
        return redirect()->back()->with('toast', trans('public.Successful Transfer Trading Account To QCG Wallet!'));
    }

    public function account_to_account(InternalTransferRequest $request)
    {
        $conn = (new CTraderService)->connectionStatus();
        if ($conn['code'] != 0) {
            if ($conn['code'] == 10) {
                return response()->json(['success' => false, 'message' => 'No connection with cTrader Server']);
            }
            return response()->json(['success' => false, 'message' => $conn['message']]);
        }

        $user = Auth::user();
        $accountNo = $request->account_no_1;
        $amount = $request->amount;
        $comment = $user->first_name . ' transfer fund to Trading Account';

        $tradingUser = TradingUser::firstWhere('meta_login', $request->account_no_1);
        (new CTraderService)->getUserInfo([$tradingUser]);
        $tradingUser = TradingUser::firstWhere('meta_login', $request->account_no_1);

        if (($tradingUser->balance - $tradingUser->credit) < $request->amount) {
            throw ValidationException::withMessages(['amount' => trans('public.Insufficient balance')]);
        }

        $payment_id = RunningNumberService::getID('transaction');
        try {
            $trade_1 = (new CTraderService)->createTrade($request->account_no_1, $request->amount, "Trading Account To Trading Account", ChangeTraderBalanceType::WITHDRAW);
            $bonus = (new CTraderService)->changeTraderBonus($accountNo, $tradingUser->bonus, $comment, ChangeTraderBalanceType::WITHDRAW);
            $credit = (new CTraderService)->createTrade($accountNo, $tradingUser->credit, $comment, ChangeTraderBalanceType::WITHDRAW_NONWITHDRAWABLE_BONUS);
        } catch (\Throwable $e) {
            if ($e->getMessage() == "Not found") {
                TradingUser::firstWhere('meta_login', $request->account_no_1)->update(['acc_status' => 'Inactive']);
            } else {
                Log::error($e->getMessage());
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
        try {
            $trade_2 = (new CTraderService)->createTrade($request->account_no_2, $request->amount, "Trading Account To Trading Account", ChangeTraderBalanceType::DEPOSIT);
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

        $commonAdjustmentData = [
            'user_id' => $user->id,
            'to' => $accountNo,
            'amount' => $amount,
            'comment' => $comment,
            'internal_description' => $comment,
            'client_description' => $comment,
            'allotted_time' => 0,
            'start_date' => Carbon::now()->format('Y-m-d'),
            'expiry_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'completed',
            'handle_by' => Auth::id(),
        ];

        // Create FundAdjustment records for bonus and credit
        FundAdjustment::create(array_merge($commonAdjustmentData, ['type' => 'bonus_out', 'amount' => $tradingUser->bonus, 'ticket' => $bonus->getTicket()]));
        FundAdjustment::create(array_merge($commonAdjustmentData, ['type' => 'credit_out', 'amount' => $tradingUser->credit, 'ticket' => $credit->getTicket()]));

        return redirect()->back()->with('toast', trans('public.Successfully Transfer Trading Account to Trading Account'));
    }
}
