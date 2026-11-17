# Возможности: новости о поездках

Страница `/opportunities` (пункт меню «Возможности») показывает посты Instagram @open_education_academy о международных
курсах и мероприятиях: фото, страна, название, короткий текст, даты, срок подачи заявок и ссылка на пост.

## Добавить посты

```bash
python tools/instagram-post.py https://www.instagram.com/p/XXXX/ https://www.instagram.com/p/YYYY/
```

Скрипт берёт публичную страницу встраивания поста (вход в Instagram не нужен), сохраняет фото в `public/img/opportunities/<код>.webp`
и добавляет запись в `resources/data/opportunities.json` с оригинальной подписью (`caption`) и датой поста.
Потом в записи заполняются `flag`, `country`, `kind`, `name`, `text` (ru/en/ro) и даты `start`, `end`, `deadline` (YYYY-MM-DD).
Список постов аккаунта Instagram без входа не отдаёт, поэтому ссылки на нужные посты передаются вручную.
