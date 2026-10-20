<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:100', 'headline' => 'nullable|string|max:180', 'bio' => 'nullable|string|max:10000',
            'location' => 'nullable|string|max:150', 'research_interests' => 'nullable|string|max:2000',
            'contact_email' => 'nullable|email|max:255', 'seo_title' => 'nullable|string|max:180', 'seo_description' => 'nullable|string|max:320',
        ];
    }
}
