{{-- Переключатель языка кабинета и страницы входа --}}
<div class="lang-sw" role="group" aria-label="{{ $tr('Язык', 'Language', 'Limba') }}">
    @foreach(['en' => 'EN', 'ru' => 'RU', 'ro' => 'RO'] as $code => $label)
        <a href="{{ route('lang', ['code' => $code, 'to' => request()->getRequestUri()]) }}" hreflang="{{ $code }}" class="{{ $lang === $code ? 'is-active' : '' }}" @if($lang === $code) aria-current="true" @endif>{{ $label }}</a>
    @endforeach
</div>
