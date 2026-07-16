<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UpdateAdvertiserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'og_image' => ['nullable', File::image()->max(config('media.max_size_kb'))],
            'remove_og_image' => ['nullable', 'boolean'],
        ];
    }
}
