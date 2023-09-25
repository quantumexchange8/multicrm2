<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $paymentPlatform = $this->input('payment_platform');

        $rules = [
            'payment_platform_name' => 'required',
            'payment_account_name' => 'required',
            'account_no' => 'required',
        ];

        if ($paymentPlatform == 'bank') {
            $rules['bank_branch_address'] = 'required';
            $rules['bank_swift_code'] = 'required';
            $rules['bank_code_type'] = 'nullable';
            $rules['bank_code'] = 'nullable';
            $rules['country'] = 'required';
            $rules['currency'] = 'required';
            $rules['proof_of_bank'] = 'required|file';
        }

        return $rules;
    }

    public function attributes(): array
    {
        $paymentPlatform = $this->input('payment_platform');

        return [
<<<<<<< Updated upstream
            'payment_platform_name' => $paymentPlatform === 'bank' ? trans('public.Bank Name') : trans('public.USDT protocol type'),
            'bank_branch_address' => trans('public.Bank Branch Address'),
            'payment_account_name' => $paymentPlatform === 'bank' ? trans('public.Bank Account Holder Name') : trans('public.USDT e-Wallet Name'),
            'account_no' => $paymentPlatform === 'bank' ? trans('public.Account No') : trans('public.Token Address'),
            'bank_swift_code' => trans('public.Bank Swift Code'),
=======
            'payment_platform_name' => $paymentPlatform === 'bank' ? trans('public.Bank Name') : 'USDT protocol type',
            'bank_branch_address' => 'Bank Branch Address',
            'payment_account_name' => $paymentPlatform === 'bank' ? 'Bank Account Holder Name' : 'USDT e-Wallet Name',
            'account_no' => $paymentPlatform === 'bank' ? 'Account No.' : 'Token Address',
            'bank_swift_code' => 'Bank Swift Code',
>>>>>>> Stashed changes
            'bank_code_type' => 'ABA / IBAN',
            'bank_code' => trans('public.Bank Code'),
            'country' => trans('public.Country'),
            'currency' => trans('public.Your Country Currency'),
            'account_type' => trans('public.USDT protocol type'),
            'proof_of_bank' => trans('public.Proof of Bank Account'),
        ];
    }
}
