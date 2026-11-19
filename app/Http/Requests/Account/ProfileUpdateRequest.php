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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'contact_phone' => trim((string) $this->input('contact_phone')),
            'contact_email' => trim((string) $this->input('contact_email')),
            'organization'  => trim((string) $this->input('organization')),
        ]);
    }

    public function rules(): array
    {
        return [
            'contact_phone'     => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s().-]{5,28}$/'],
            'contact_email'     => ['nullable', 'email:rfc', 'max:120'],
            'organization'      => ['nullable', 'string', 'max:160'],
            'full_name'   => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
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
            'contact_phone.regex'     => \App\Support\Lang::t('Телефон: цифры, пробелы и знаки + ( ) -, например +373 600 00000.', 'Phone: digits, spaces and + ( ) -, for example +373 600 00000.', 'Telefon: cifre, spații și semnele + ( ) -, de exemplu +373 600 00000.'),
            'contact_email.email'     => \App\Support\Lang::t('Введите корректный адрес почты.', 'Enter a valid email address.', 'Introdu o adresă de e-mail validă.'),
            'organization.max'        => \App\Support\Lang::t('Не более 160 символов.', 'At most 160 characters.', 'Cel mult 160 de caractere.'),
            'full_name.required' => \App\Support\Lang::t('Введите имя и фамилию.', 'Enter your full name.', 'Introdu numele și prenumele.'),
            'full_name.max'      => \App\Support\Lang::t('Имя не длиннее 120 символов.', 'The name can be at most 120 characters.', 'Numele poate avea cel mult 120 de caractere.'),
            'description.max'    => $text,
            'facts.max'          => $many,
            'new_facts.max'      => $many,
            'facts.*.max'        => $fact,
            'new_facts.*.max'    => $fact,
        ];
    }
}
