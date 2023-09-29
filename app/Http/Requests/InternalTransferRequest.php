<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InternalTransferRequest extends FormRequest
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
            'account_no' => ['required', 'string'],
            'account_no_1' => ['required', 'string'],
            'account_no_2' => ['required', 'string', 'different:account_no_1'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];

    }

    public function attributes(): array
    {
        return [
            'account_no' => trans('public.Account No'),
            'account_no_1' => trans('public.Account to Transfer'),
            'account_no_2' => trans('public.Account to Receive'),
            'amount' => trans('public.Amount'),
        ];
    }
}
