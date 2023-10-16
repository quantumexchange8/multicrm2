<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InternalTransferRequest extends FormRequest
{

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
        $transferType = $this->input('transfer_type');
        
        $rules = [
            'account_no' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];

        if ($transferType == 'ATA') {
            $rules['account_no_1'] = 'required|string';
            $rules['account_no_2'] = 'required|string|different:account_no_1';
        }

        return $rules;
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
