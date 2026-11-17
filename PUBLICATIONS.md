# Публикации (листалка)

Страница `/publications` показывает издания, `/publications/<slug>` открывает читалку с листанием (разворот книги, приближение, полный экран).
Посетителю уходят только лёгкие WebP-картинки страниц, они подгружаются по мере листания: PDF на сайте не нужен.

## Добавить новое издание

1. Положите PDF куда угодно на своём компьютере (папка `user/` в git не попадает).
2. Один раз выполните (нужны `pip install pymupdf pillow`):

   ```bash
   python tools/pdf-to-pages.py "user/публикации/файл.pdf" kratkoe-imya-latinicei
   ```

   Скрипт сделает `public/img/publications/<slug>/001.webp, 002.webp, …` и `cover.webp`, а в `resources/data/publications.json`
   добавит запись с числом страниц и пропорцией листа.
3. В `resources/data/publications.json` впишите `year`, `title` и `description` на ru / en / ro.
4. Закоммитьте `public/img/publications/<slug>/` и `resources/data/publications.json`, на сервере `git pull`.

Размер: около 0,2 МБ на страницу с фото и 5–20 КБ на текстовую (сборник 2025: 228 страниц, 51 МБ).
Если страниц слишком много, уменьшите `--width` (по умолчанию 1240) или `--quality` (74).

Кнопки «Скачать PDF» нет намеренно: исходник весит сотни мегабайт.
Листалка: `public/design/flipbook.js` + `flipbook.css`, библиотека `page-flip.browser.js` (StPageFlip, MIT).
