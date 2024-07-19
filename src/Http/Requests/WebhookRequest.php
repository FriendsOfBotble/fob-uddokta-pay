<?php

namespace FriendsOfBotble\UddoktaPay\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebhookRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => 'required',
            'email' => 'required',
            'amount' => 'required',
            'invoice_id' => 'required',
            'metadata' => 'required',
            'payment_method' => 'required',
            'sender_number' => 'required',
            'transaction_id' => 'required',
            'status' => 'required',
        ];
    }
}
