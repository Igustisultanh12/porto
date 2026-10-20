<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug') ?: $this->input('title', '');
        if (is_string($slug)) {
            $this->merge(['slug' => Str::slug($slug)]);
        }
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['project', 'research', 'article'])], 'title' => 'required|string|max:180',
            'slug' => ['required', 'string', 'max:190', Rule::unique('contents')->ignore($this->route('content')?->id)],
            'summary' => 'nullable|string|max:2000', 'body' => 'nullable|string|max:100000',
            'status' => ['required', Rule::in(['draft', 'published'])], 'featured' => 'required|boolean', 'sort_order' => 'required|integer|min:0|max:100000',
            'year' => 'nullable|integer|min:1900|max:2100',
            'publication_type' => ['nullable', Rule::in(['journal', 'conference', 'thesis', 'preprint', 'report', 'other'])],
            'seo_title' => 'nullable|string|max:180', 'seo_description' => 'nullable|string|max:320',
            'taxonomy_ids' => 'array|max:50', 'taxonomy_ids.*' => 'integer|distinct|exists:taxonomies,id',
            'metadata' => 'nullable|array:role,period,problem,approach,result,demo_url,repository_url,dataset_url,code_url,venue,doi,academic_status,authors,publication_date',
            'metadata.role' => 'nullable|string|max:200', 'metadata.period' => 'nullable|string|max:100',
            'metadata.problem' => 'nullable|string|max:10000', 'metadata.approach' => 'nullable|string|max:10000', 'metadata.result' => 'nullable|string|max:10000',
            'metadata.demo_url' => 'nullable|url:http,https|max:2048', 'metadata.repository_url' => 'nullable|url:http,https|max:2048',
            'metadata.dataset_url' => 'nullable|url:http,https|max:2048', 'metadata.code_url' => 'nullable|url:http,https|max:2048',
            'metadata.venue' => 'nullable|string|max:255', 'metadata.doi' => ['nullable', 'string', 'max:255', 'regex:~^10\.\d{4,9}/\S+$~'],
            'metadata.academic_status' => ['nullable', Rule::in(['preprint', 'submitted', 'accepted', 'published'])],
            'metadata.publication_date' => 'nullable|date_format:Y-m-d',
            'metadata.authors' => 'nullable|array|max:40', 'metadata.authors.*' => 'array:name,affiliation',
            'metadata.authors.*.name' => 'required|string|max:150', 'metadata.authors.*.affiliation' => 'nullable|string|max:255',
        ];
    }
}
