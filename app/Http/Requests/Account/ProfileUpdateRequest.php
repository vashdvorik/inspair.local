<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('account_telegram_id');
    }

    public function rules(): array
    {
        return [
            'full_name'   => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'expectation' => ['nullable', 'string', 'max:1000'],
            'facts'       => ['nullable', 'array', 'max:10'],
            'facts.*'     => ['nullable', 'string', 'max:300'],
            'new_facts'   => ['nullable', 'array', 'max:10'],
            'new_facts.*' => ['nullable', 'string', 'max:300'],
        ];
    }
}
