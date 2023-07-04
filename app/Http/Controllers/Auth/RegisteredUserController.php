<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Mail\SendOtp;
use App\Models\AccountType;
use App\Models\SettingCountry;
use App\Models\SettingLeverage;
use App\Models\User;
use App\Models\VerifyOtp;
use App\Providers\RouteServiceProvider;
use App\Services\CTraderService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $countries = SettingCountry::all();
        $leverages = SettingLeverage::all();

        return Inertia::render('Auth/Register', [
            'countries' => $countries,
            'leverages' => $leverages
        ]);
    }

    public function firstStep(Request $request)
    {
        $rules = [
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'mobile' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:8',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        $attributes = [
            'email' => 'Email',
            'mobile' => 'Mobile',
            'password' => 'Password',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($attributes);

        if ($request->form_step == 1) {
            $validator->validate();
        } elseif ($request->form_step == 2) {
            $additionalRules = [
                'name' => 'required|regex:/^[a-zA-Z0-9\p{Han}. ]+$/u|max:255',
                'chinese_name' => 'nullable|regex:/^[a-zA-Z0-9\p{Han}. ]+$/u',
                'dateOfBirth' => 'required',
                'country' => 'required'
            ];
            $rules = array_merge($rules, $additionalRules);

            $additionalAttributes = [
                'name' => 'Full Name',
                'chinese_name' => 'Chinese Name',
                'dateOfBirth' => 'Date of Birth',
                'country' => 'Country',
            ];
            $attributes = array_merge($attributes, $additionalAttributes);

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($attributes);
            $validator->validate();
        } elseif ($request->form_step == 3) {
            $additionalRules = [
                'account_platform' => 'required',
                'account_type' => 'required',
                'leverage' => 'required',
            ];
            $rules = array_merge($rules, $additionalRules);

            $additionalAttributes = [
                'account_platform' => 'Account Platform',
                'account_type' => 'Account Type',
                'leverage' => 'Leverage',
            ];
            $attributes = array_merge($attributes, $additionalAttributes);

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($attributes);
            $validator->validate();
        }

        return to_route('register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        dd($request->all());
        $conn = (new CTraderService)->connectionStatus();

        if ($conn['code'] != 0) {
            if ($conn['code'] == 10) {
                return back()->with('error_message', 'No connection with cTrader Server');
            }
            return back()->with('error_message', $conn['message']);
        }

        if($request->verification_via == 'email'){
            $verificationType = $request->email;
        }else{
            $verificationType = $request->mobile;
        }

        $otp = VerifyOtp::where($request->verification_via, $verificationType)->first();

        $expirationTime = Carbon::parse($otp->updated_at)->addMinutes(5);

        if (Carbon::now()->greaterThan($expirationTime)) {
            throw ValidationException::withMessages([
                'verification_code' => 'The Verification OTP Code expired.'
            ]);
        }

        if($otp->otp != $request->verification_code){
            throw ValidationException::withMessages(['verification_code' => trans('validation.in', ['attribute' => 'otp'])]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'country' => $request->country,
            'dob' => $request->dateOfBirth,
            'register_ip' => $request->ip(),
            'password' => Hash::make($request->password),
        ]);

        $mainPassword = Str::random(8);
        $investorPassword = Str::random(8);

        $group = AccountType::with('metaGroup')->where('id', $request->account_type)->get()->value('metaGroup.meta_group_name');
        $leadCampaign = null;
        $leadSource = null;
        $remarks = 'vietnam plan';
        $ctUser = (new CTraderService)->CreateCTID($user->email);
        $user->update(['ct_user_id' => $ctUser['userId']]);
        $user = User::find($user->id);
        $ctAccount = (new CTraderService)->createUser($user, $mainPassword, $investorPassword, $group, $request->leverage, $request->account_type, $leadCampaign, $leadSource, $remarks);
        $user->update(['remark' => $remarks]);
//        event(new Registered($user));
//
//        Auth::login($user);

        return redirect('/login')->with('toast', 'Successfully Created Account');
    }

    public function sendOtp(Request $request)
    {
        $otp = $request->input('otp');
        $email = $request->input('email');

        // Save the OTP and email using the VerifyOtp model
        $verfiy_otp = VerifyOtp::updateOrCreate([
            'email' => $email,
        ], [
            'otp' => $otp,
        ]);

        $isSent = Mail::to($email)->send(new SendOtp($verfiy_otp->otp));

        if ($isSent)
        {
            return response()->json(['message' => 'OTP sent successfully']);
        }
        else
        {
            return response()->json(['message' => 'OTP failed']);
        }
    }
}
