<?php

namespace App\Http\Requests;

class UpdateListingRequest extends StoreListingRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
        ];
    }
}
