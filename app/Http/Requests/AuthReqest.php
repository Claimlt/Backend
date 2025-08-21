<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthReqest extends FormRequest
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
            'nic' => 'required|string|digits:10',
        ];
    }
}
