<?php

namespace App\Http\Requests;

use App\Enums\ListingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'size:2'],
            'contact_name' => ['required', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email:rfc', 'max:160'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::enum(ListingStatus::class)],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['required', File::image()->max(config('media.max_size_kb'))],
        ];
    }
}
