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

    public function messages(): array
    {
        $fact = \App\Support\Lang::t('Факт не длиннее 300 символов.', 'A fact can be at most 300 characters.', 'Un fapt poate avea cel mult 300 de caractere.');
        $many = \App\Support\Lang::t('Слишком много фактов.', 'Too many facts.', 'Prea multe fapte.');
        $text = \App\Support\Lang::t('Не более 1000 символов.', 'At most 1000 characters.', 'Cel mult 1000 de caractere.');

        return [
            'full_name.required' => \App\Support\Lang::t('Введите имя и фамилию.', 'Enter your full name.', 'Introdu numele și prenumele.'),
            'full_name.max'      => \App\Support\Lang::t('Имя не длиннее 120 символов.', 'The name can be at most 120 characters.', 'Numele poate avea cel mult 120 de caractere.'),
            'description.max'    => $text,
            'expectation.max'    => $text,
            'facts.max'          => $many,
            'new_facts.max'      => $many,
            'facts.*.max'        => $fact,
            'new_facts.*.max'    => $fact,
        ];
    }
}
