<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\DepositRequest;
use App\Http\Requests\Payment\WithdrawalRequest;
use App\Models\GatewayExchangeRate;
use App\Models\Payment;
use App\Models\SettingCryptoWallet;
use App\Models\TradingAccount;
use App\Models\TradingUser;
use App\Models\User;
use App\Services\ChangeTraderBalanceType;
use App\Services\CTraderService;
use App\Services\RunningNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentController extends Controller
{

    /*  private $Status = array(
        0 => 'Waiting for payment',
        1 => 'Payment Approved',
        2 => 'Rejected',
    ); */
    private $Status = array(
        0 => 'Submitted',
        1 => 'Successful',
        2 => 'Rejected',
    );

    private $currency = ['USD', 'MYR', 'IDR', 'RMB', 'PHP', 'SGD', 'VND', 'THB', 'HKD', 'INR', 'USDT'];
    //
    private $merchantID = "60-00000125-65107369";
    private $apiKey = "4FF6B347-8A7C-4779-92F5-098706110CFD";
    private $secretKey = "0883EF96B3314A8B865DA4E3A16E4829";
    private $base_url = "http://api.doitwallet.asia";


    public function get_trading_account()
    {
        $conn = (new CTraderService)->connectionStatus();
        $user = Auth::user();
        $cryptoWallets = SettingCryptoWallet::with('media')
            ->where('status', SettingCryptoWallet::STATUS_ACTIVE)
            ->get();

        if ($conn['code'] == 0) {
            (new CTraderService)->getUserInfo($user->tradingUsers);
            $trading_account = TradingAccount::where('user_id', Auth::id())->with(['accountType'])->get();
            $payment_account = TradingUser::where('user_id', $user->id)->whereNot('module', 'pamm')->get();
        } else {
            return redirect()->back()->with('toast', 'No Connection with CTrader');
        }
//        $trading_account = TradingAccount::where('user_id', Auth::id())->with(['accountType'])->get();
//        $payment_account = TradingUser::where('user_id', $user->id)->whereNot('module', 'pamm')->get();
        return response()->json([
            'tradingAccounts' => $trading_account,
            'paymentAccounts' => $payment_account,
            'cryptoWallets' => $cryptoWallets,
        ]);
    }

    public function deposit(DepositRequest $request)
    {
        $conn = (new CTraderService)->connectionStatus();
        if ($conn['code'] != 0) {
            if ($conn['code'] == 10) {
                return back()->withErrors(['connection' => ['No connection with cTrader Server']]);
            }
            return back()->withErrors(['connection' => [$conn['message']]]);
        }
        /*  $date = date('Y/m/d h:i:s a', time());
        $date = (int) filter_var($date, FILTER_SANITIZE_NUMBER_INT);
        $payment_id = "DEMOORDER_" . $date; */

        $meta_login = $request->account_no;
        $amount = number_format($request->amount, 2, '.', '');

        $payment_id = RunningNumberService::getID('transaction');

        $currency = $request->currency;


        $payment_charges = null;
        $real_amount = $amount;
        $exchange_rate =  GatewayExchangeRate::whereRelation('ofGateway', 'name', '=', 'ompay')
            ->where('base_currency', $currency)
            ->where('target_currency', 'USD')
            ->where('status', 'Active')
            ->first();
        if ($exchange_rate) {

            switch ($exchange_rate->deposit_charge_type) {
                case 'percentage': {
                    $payment_charges = $exchange_rate->deposit_charge_amount . '%';

                    $real_amount = number_format(($amount * $exchange_rate->deposit_rate) * ((100 + $exchange_rate->deposit_charge_amount) / 100), 2, '.', '');
                    break;
                }
                case 'amount': {
                    $payment_charges = $currency . ' ' . $exchange_rate->deposit_charge_amount;
                    $real_amount = number_format(($amount * $exchange_rate->deposit_rate) + $exchange_rate->deposit_charge_amount, 2, '.', '');
                    break;
                }
            }
        }
        $apiUrl = $this->base_url . "/Merchant/Pay";
        $user = Auth::user();


        Payment::create([
            'to' => $meta_login,
            'user_id' => $user->id,
            'category' => 'payment',
            'payment_id' => $payment_id,
            'type' => 'Deposit',
            'comment' => 'Deposit',
            'amount' => $amount,
            'gateway' => 'ompay',
            'currency' => $currency,
            'real_amount' => $real_amount,
            'payment_charges' => $payment_charges,
        ]);

        $returnUrl = url('ompay/depositResult?token=' . session()->get('jwt-token'));
        $notifyUrl = url('ompay/updateStatus');
        $token = md5($payment_id . $this->apiKey . $this->secretKey . $real_amount);
        $mode = 3;

        $redirectUrl = $apiUrl . "?mode={$mode}&merchantCode={$this->merchantID}&serialNo={$payment_id}&currency={$currency}&amount={$real_amount}&returnUrl={$returnUrl}&notifyUrl={$notifyUrl}&token={$token}";

        return Inertia::location($redirectUrl);

    }

    public function depositResult(Request $request)
    {
        $data = $request->all();

        Log::info('Deposit Result' . ": " . json_encode($data));
        if ($request->token) {
            session()->put('jwt-token', $request->token);
        }
        return redirect()->back();
    }

    public function updateResult(Request $request)
    {
        $data =  $request->all();
        Log::info('Update Status' . ": " . json_encode($data));
        $result = [
            "Info" => $data['Info'],
            "MerchantCode" => $data['MerchantCode'],
            "SerialNo" => $data["SerialNo"],
            "CurrencyCode" => $data["CurrencyCode"],
            "Amount" => $data["Amount"],
            "Status" => $data['Status'],
            "Token" => $data["Token"],
        ];

        Log::info('Update Status Result' . ": " . json_encode($result));
        if ($result["Token"] == md5($result['SerialNo'] . $this->apiKey . $this->secretKey)) {
            $payment = Payment::query()->where('payment_id', Str::upper($result['SerialNo']))->first();
            if ($payment->status == "Submitted" || $payment->status == "Processing") {
                if ($result['Status'] == 1) {
                    try {
                        $trade = (new CTraderService)->createTrade($payment->to, $payment->amount, $payment->comment, ChangeTraderBalanceType::DEPOSIT);
                        $payment->ticket = $trade->getTicket();

                        $user = User::find($payment->user_id);
                        $user->total_deposit += $payment->amount;
                        $user->save();
                    } catch (\Throwable $e) {
                        if ($e->getMessage() == "Not found") {
                            TradingUser::firstWhere('meta_login', $payment->to)->update(['acc_status' => 'Inactive']);
                        }
                        Log::error($e->getMessage() . " " . $payment->payment_id);
                    }
                }
                $payment->status = $this->Status[$result['Status']];
                $payment->save();
            }
        }
    }

    public function requestWithdrawal(WithdrawalRequest $request)
    {
            $user = Auth::user();
            $amount = floatval($request->amount);
            if ($user->cash_wallet < $amount) {
                return response()->json(['success' => false, 'message' => 'Insufficient balance']);
            }
            $user->cash_wallet -= $amount;
            $user->save();
            $payment_id = RunningNumberService::getID('transaction');

            Payment::create([
                'user_id' => $user->id,
                'payment_id' => $payment_id,
                'category' => 'payment',
                'type' => 'Withdrawal',
                'channel' => $request->channel,
                'amount' => $amount,
                'account_no' => $request->account_no,
                'account_type' => $request->account_type,
            ]);

            return redirect()->back()->with('toast', 'Successfully Submitted Withdrawal Request');
    }
}
