<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'submission_id' => 'required|uuid', 'name' => 'required|string|max:100', 'email' => 'required|email|max:255',
            'subject' => 'required|string|max:160', 'message' => 'required|string|min:10|max:10000',
            'website' => 'nullable|string|max:0',
        ];
    }
}
