<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSoundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by Filament
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'audio_file' => [
                'required',
                'file',
                'mimes:mp3,mp4,wav',
                'max:10240', // 10MB
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'audio_file.required' => 'Please upload an audio file.',
            'audio_file.mimes' => 'The audio file must be in MP3, MP4, or WAV format.',
            'audio_file.max' => 'The audio file size cannot exceed 10MB.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
        ];
    }
}
