<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Anyone can register
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s\-\'\.]+$/', // Only letters, spaces, hyphens, apostrophes, and dots
            ],
            'country_code' => [
                'required',
                'string',
                'in:+62,+1,+44,+81,+86,+91,+33,+49,+39,+34,+61,+7,+55,+31,+32,+46,+47,+48,+52,+54,+56,+57,+58,+60,+61,+62,+63,+64,+65,+66,+67,+68,+69,+70,+71,+72,+73,+74,+75,+76,+77,+78,+79,+80,+81,+82,+83,+84,+85,+86,+87,+88,+89,+90,+91,+92,+93,+94,+95,+96,+97,+98,+99',
            ],
            'phone' => [
                'required',
                'string',
                'min:8',
                'max:15',
                'regex:/^[0-9]+$/',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'gender' => [
                'nullable',
                'string',
                'in:male,female',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validatePhoneWithCountryCode($validator);
        });
    }

    /**
     * Custom validation for phone number with country code.
     */
    protected function validatePhoneWithCountryCode($validator)
    {
        $countryCode = $this->input('country_code');
        $phone = $this->input('phone');

        if (!$countryCode || !$phone) {
            return;
        }

        // Validate phone number format based on country code
        if (!$this->isValidPhoneForCountry($countryCode, $phone)) {
            $validator->errors()->add('phone', 'Format nomor telepon tidak valid untuk kode negara yang dipilih.');
        }
    }

    /**
     * Check if phone number is valid for the given country code.
     */
    protected function isValidPhoneForCountry(string $countryCode, string $phone): bool
    {
        // Remove + from country code for comparison
        $code = str_replace('+', '', $countryCode);

        // Phone validation rules for different countries
        $rules = [
            '62' => [ // Indonesia
                'min_length' => 8,
                'max_length' => 12,
                'prefixes' => ['2', '3', '8', '9'], // Valid prefixes for Indonesia
                'pattern' => '/^[2-389][0-9]{7,11}$/'
            ],
            '1' => [ // US/Canada
                'min_length' => 10,
                'max_length' => 10,
                'pattern' => '/^[0-9]{10}$/'
            ],
            '44' => [ // UK
                'min_length' => 10,
                'max_length' => 11,
                'pattern' => '/^[0-9]{10,11}$/'
            ],
            '81' => [ // Japan
                'min_length' => 9,
                'max_length' => 10,
                'pattern' => '/^[0-9]{9,10}$/'
            ],
            '86' => [ // China
                'min_length' => 11,
                'max_length' => 11,
                'pattern' => '/^1[0-9]{10}$/'
            ],
            '91' => [ // India
                'min_length' => 10,
                'max_length' => 10,
                'pattern' => '/^[6-9][0-9]{9}$/'
            ],
        ];

        // If country code not in rules, use default validation
        if (!isset($rules[$code])) {
            return strlen($phone) >= 8 && strlen($phone) <= 15;
        }

        $rule = $rules[$code];

        // Check length
        if (strlen($phone) < $rule['min_length'] || strlen($phone) > $rule['max_length']) {
            return false;
        }

        // Check pattern if exists
        if (isset($rule['pattern']) && !preg_match($rule['pattern'], $phone)) {
            return false;
        }

        // Check prefixes if exists
        if (isset($rule['prefixes'])) {
            $firstDigit = substr($phone, 0, 1);
            if (!in_array($firstDigit, $rule['prefixes'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'name.min' => 'Nama minimal 2 karakter.',
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, tanda hubung, apostrof, dan titik.',

            'country_code.required' => 'Kode negara wajib diisi.',
            'country_code.in' => 'Kode negara tidak valid.',

            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.string' => 'Nomor telepon harus berupa teks.',
            'phone.phone' => 'Format nomor telepon tidak valid untuk kode negara yang dipilih.',
            'phone.min' => 'Nomor telepon minimal 8 digit.',
            'phone.max' => 'Nomor telepon maksimal 15 digit.',

            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',

            'gender.in' => 'Jenis kelamin harus male atau female.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'country_code' => 'kode negara',
            'phone' => 'nomor telepon',
            'email' => 'email',
            'gender' => 'jenis kelamin',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default country code if not provided or format country code
        if (!$this->has('country_code') || empty($this->country_code)) {
            $this->merge([
                'country_code' => '+62',
            ]);
        } else {
            // Ensure country code has + prefix
            $countryCode = $this->country_code;
            if (!str_starts_with($countryCode, '+')) {
                $countryCode = '+' . $countryCode;
            }
            $this->merge([
                'country_code' => $countryCode,
            ]);
        }

        // Clean phone number (remove spaces and special characters)
        if ($this->has('phone')) {
            $this->merge([
                'phone' => trim($this->phone),
            ]);
        }

        // Clean name (trim whitespace)
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }

        // Clean email (trim whitespace and convert to lowercase)
        if ($this->has('email')) {
            if ($this->email && $this->email !== 'undefined' && $this->email !== 'null') {
                $this->merge([
                    'email' => strtolower(trim($this->email)),
                ]);
            } else {
                $this->merge([
                    'email' => null,
                ]);
            }
        }
    }
}
