<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\DepositRequest;
use App\Http\Requests\Payment\WithdrawalRequest;
use App\Models\GatewayExchangeRate;
use App\Models\IbAccountType;
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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

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

        $returnUrl = url('/dashboard');
        $notifyUrl = url('ompay/updateStatus');
        if ($currency == 'MYR')
        {
            $token = md5($payment_id . '78F6F83E-1DE9-4BC2-8253-8319C1A4F104' . '645B45FF789C431889BFFBF1E462DCE6' . $real_amount);
            $mode = 3;

            $redirectUrl = $apiUrl . "?mode={$mode}&merchantCode=60-00000197-89382959&serialNo={$payment_id}&currency={$currency}&amount={$real_amount}&returnUrl={$returnUrl}&notifyUrl={$notifyUrl}&token={$token}";
        } else {
            $token = md5($payment_id . $this->apiKey . $this->secretKey . $real_amount);
            $mode = 3;

            $redirectUrl = $apiUrl . "?mode={$mode}&merchantCode={$this->merchantID}&serialNo={$payment_id}&currency={$currency}&amount={$real_amount}&returnUrl={$returnUrl}&notifyUrl={$notifyUrl}&token={$token}";
        }

        Log::info($redirectUrl);

        return Inertia::location($redirectUrl);

    }

    public function depositResult(Request $request)
    {
        $data = $request->all();
        Log::debug($data);
        if ($request->token) {
            session()->put('jwt-token', $request->token);
        }

        return to_route('dashboard');
    }

    public function updateResult(Request $request)
    {
        $data =  $request->all();
        Log::debug($data);
        $result = [
            "Info" => $data['Info'],
            "MerchantCode" => $data['MerchantCode'],
            "SerialNo" => $data["SerialNo"],
            "CurrencyCode" => $data["CurrencyCode"],
            "Amount" => $data["Amount"],
            "Status" => $data['Status'],
            "Token" => $data["Token"],
        ];
        Log::debug($result);

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
            throw ValidationException::withMessages(['amount' => trans('Insufficient balance')]);
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

        return back()->with('toast', 'Successfully Submitted Withdrawal Request');
    }

    public function applyRebate(Request $request)
    {
        $accountType = IbAccountType::where('user_id', Auth::id())->where('account_type', $request->account_type)->with('accountType')->first();
        $user = User::find(Auth::id());
        if ($accountType->rebate_wallet <= 0) {
            return response()->json([
                'success' => false,
                'message' => trans('Insufficient balance to apply the rebate. You have not earned any rebate yet.')
            ], 422);
        }

        $payment_id = RunningNumberService::getID('transaction');

        Payment::create([
            'user_id' => $user->id,
            'payment_id' => $payment_id,
            'category' => 'apply_rebate',
            'type' => '',
            'amount' => $accountType->rebate_wallet,
            'status' => 'Successful',
        ]);
        $user->cash_wallet = number_format($user->cash_wallet + $accountType->rebate_wallet, 2, '.', '');
        $user->save();
        $accountType->update(['rebate_wallet' => 0, 'trade_lot' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Congratulation, we have received your rebate request. The rebate will be transferred to your cash wallet shortly. Once processed, you will be able to withdraw or transfer your funds.',
            'cash_wallet' => $user->cash_wallet,
            'rebate_wallet' => $accountType->rebate_wallet
        ]);
    }
}
