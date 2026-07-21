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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'state' => str($this->input('state'))->upper()->toString(),
            'accepts_offers' => $this->boolean('accepts_offers'),
            'quick_sale' => $this->boolean('quick_sale'),
            'negotiable_price' => $this->boolean('negotiable_price'),
            'easy_pickup' => $this->boolean('easy_pickup'),
        ]);
    }

    public function rules(): array
    {
        $state = str($this->input('state'))->upper()->toString();

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'accepts_offers' => ['boolean'],
            'quick_sale' => ['boolean'],
            'negotiable_price' => ['boolean'],
            'easy_pickup' => ['boolean'],
            'city' => [
                'required',
                'string',
                'max:120',
                Rule::exists('cities', 'name')->where('state_code', $state),
            ],
            'state' => ['required', 'string', 'size:2', Rule::exists('states', 'code')],
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
