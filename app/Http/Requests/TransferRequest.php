<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            "sender_id"   => "required|integer|exists:users,id",
            "receiver_id" => "required|integer|exists:users,id|different:sender_id",
            "amount"      => "required|numeric|min:0.01",
        ];
    }
}
