<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['string', 'max:255'],
            'chinese_name' => ['nullable', 'string', 'max:255'],
            'dob' => ['required'],
            'country' => ['string', 'max:255'],
            'phone' => ['string', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'email' => ['email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'avatar' => ['nullable', 'image', 'max:10240'],
            'front_identity' => ['nullable', 'image', 'max:10240'],
            'back_identity' => ['nullable', 'image', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => trans('public.Name'),
            'chinese_name' => trans('public.Chinese Name'),
            'dob' => trans('public.Date Of Birth'),
            'country' => trans('public.Country'),
            'phone' => trans('public.Phone'),
            'email' => trans('public.Email'),
            'avatar' => trans('public.Profile Photo'),
            'front_identity' => trans('public.Proof of Identity (Front)'),
            'back_identity' => trans('public.Proof of Identity (Back)'),
        ];
    }
}
