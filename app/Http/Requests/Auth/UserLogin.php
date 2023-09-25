<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;

class UserLogin extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username'           => 'required|email|max:128|exists:users,email',
            'password'        => 'required|string|min:6',
        ];
    }

    public function attributes()
    {
        return [
            'username' => trans('public.Email'),
            'password' => trans('public.Password'),
        ];
    }
}
