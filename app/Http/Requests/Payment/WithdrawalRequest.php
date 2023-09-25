<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
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
        return [
            'account_no' => 'required|string',
            'account_type' => 'required|string',
            'amount' => 'required|numeric|min:30',
            'channel' => 'required|string',
            'terms' => 'required',
        ];
    }

    public function attributes(): array
    {
        $attributeNames = [
            'amount' => trans('public.Amount'),
            'channel' => trans('public.Withdrawal Method'),
            'terms' => trans('public.Terms and Conditions'),
        ];

        if ($this->input('channel') =='bank') {
            $attributeNames['account_no'] = trans('public.Bank Account');
            $attributeNames['account_type'] = trans('public.Bank');
        } else {
            $attributeNames['account_no'] = trans('public.Cryptocurrency Address');
            $attributeNames['account_type'] = trans('public.Cryptocurrency');
        }

        return $attributeNames;
    }
}
