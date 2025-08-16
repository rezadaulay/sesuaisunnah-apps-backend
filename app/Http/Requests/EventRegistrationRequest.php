<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow public registration
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', 'in:male,female'],
            'phone' => [
                'required', 
                'string', 
                'max:20',
                'regex:/^(\+62|62|0)8[1-9][0-9]{6,9}$/'
            ],
            'email' => ['nullable', 'email', 'max:150'],
            'referral_source' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'Event harus dipilih.',
            'event_id.exists' => 'Event yang dipilih tidak valid.',
            'name.required' => 'Nama harus diisi.',
            'name.max' => 'Nama maksimal 150 karakter.',
            'gender.required' => 'Jenis kelamin harus dipilih.',
            'gender.in' => 'Jenis kelamin harus male atau female.',
            'phone.required' => 'Nomor telepon harus diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan format Indonesia.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 150 karakter.',
            'referral_source.max' => 'Sumber referral maksimal 150 karakter.',
        ];
    }

    /**
     * Get custom attribute names for validation rules.
     */
    public function attributes(): array
    {
        return [
            'event_id' => 'Event',
            'name' => 'Nama',
            'gender' => 'Jenis Kelamin',
            'phone' => 'Nomor Telepon',
            'email' => 'Email',
            'referral_source' => 'Sumber Referral',
        ];
    }
}
