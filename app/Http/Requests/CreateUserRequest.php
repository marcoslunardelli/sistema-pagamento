<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            "name"      => "required|string|min:3",
            "email"     => "required|email|unique:users,email",
            "cpf_cnpj"  => "required|string|unique:users,cpf_cnpj",
            "type"      => "required|in:comum,lojista",
            "password"  => "required|string|min:6",
            "balance"   => "required|numeric|min:0",
        ];
    }
}
