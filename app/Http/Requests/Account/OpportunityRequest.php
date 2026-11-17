<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class OpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('account_telegram_id');
    }

    public function rules(): array
    {
        return [
            'type'        => ['required', 'in:project,meeting,event'],
            'title'       => ['required', 'string', 'max:200'],
            'body'        => ['required', 'string', 'max:2000'],
            'event_date'  => ['nullable', 'date', 'after_or_equal:today'],
            'location'    => ['nullable', 'string', 'max:200'],
            'contact_url' => ['nullable', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'             => \App\Support\Lang::t('Выберите тип возможности.', 'Choose the type of opportunity.', 'Alege tipul oportunității.'),
            'type.in'                   => \App\Support\Lang::t('Недопустимый тип.', 'Invalid type.', 'Tip invalid.'),
            'title.required'            => \App\Support\Lang::t('Введите заголовок.', 'Enter a title.', 'Introdu un titlu.'),
            'title.max'                 => \App\Support\Lang::t('Заголовок не более 200 символов.', 'The title can be at most 200 characters.', 'Titlul poate avea cel mult 200 de caractere.'),
            'body.required'             => \App\Support\Lang::t('Введите описание.', 'Enter a description.', 'Introdu o descriere.'),
            'body.max'                  => \App\Support\Lang::t('Описание не более 2000 символов.', 'The description can be at most 2000 characters.', 'Descrierea poate avea cel mult 2000 de caractere.'),
            'event_date.date'           => \App\Support\Lang::t('Введите корректную дату.', 'Enter a valid date.', 'Introdu o dată validă.'),
            'event_date.after_or_equal' => \App\Support\Lang::t('Дата не может быть в прошлом.', 'The date cannot be in the past.', 'Data nu poate fi în trecut.'),
            'contact_url.url'           => \App\Support\Lang::t('Введите корректный URL (https://...).', 'Enter a valid URL (https://...).', 'Introdu un URL valid (https://...).'),
            'contact_url.max'           => \App\Support\Lang::t('URL не более 500 символов.', 'The URL can be at most 500 characters.', 'URL-ul poate avea cel mult 500 de caractere.'),
        ];
    }
}
