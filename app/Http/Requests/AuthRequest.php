<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:user',
            'password' => 'required|string|min:6|confirmed',
            'contact_number' => "required|string|digits:10",
            'nic' => 'required|string|digits:12',
        ];

    }
    public function messages()
    {
        return [
            'nic.unique' => 'This NIC is already registered',
            'email.unique' => 'This email is already registered',
        ];
    }
}
