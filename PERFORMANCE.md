# Скорость сайта и кабинета

## Что уже сделано в коде

| Что | Эффект |
|---|---|
| Публичные страницы работают **без сессии** (`routes/web.php`, группа `withoutMiddleware`) | 0 запросов к базе и ни одной куки вместо 2–3 запросов на каждый заход |
| Сессии и кэш по умолчанию в **файлах**, а не в базе (`config/session.php`, `config/cache.php`, `.env.example`) | кабинет: 1–2 запроса к базе вместо 3–4, вход по токену 2 вместо 15 |
| **Тяжёлые пакеты не грузятся там, где не нужны**: Filament (админка), Livewire, Nutgram (бот) подключаются только для `/admin`, `/livewire…`, `/telegram…` (`AppServiceProvider::registerHeavyPackages`, `composer.json → dont-discover`) | запрос к сайту и кабинету стартует на 35–40% быстрее |
| Alpine.js лежит на сайте (`public/design/alpine.min.js`), а не грузится с чужого CDN | на одно соединение с внешним сервером меньше при открытии кабинета |
| Игра: число «готовых участников» кэшируется на минуту, отметка «был в игре» пишется не чаще раза в минуту | меньше запросов и записей в базу при каждом опросе |

## Что нужно сделать на сервере (самое важное)

Код сайта сам по себе отвечает за ~10–15 мс. Остальное это запуск PHP-приложения, и на хостинге его почти целиком определяют настройки:

1. **OPcache должен быть включён.** Без него приложение каждый раз заново читает и разбирает сотни файлов: запрос в 2 раза дольше (замер на этом компьютере: ~190 мс без, ~95 мс с OPcache). В cPanel: «Select PHP Version» → «Extensions» → отметить `opcache`. Проверка в терминале cPanel:

   ```bash
   /usr/local/bin/ea-php83 -i | grep -E "^opcache.enable |Zend OPcache"
   ```

2. **Файл `.env` на сервере:**

   ```
   APP_ENV=production
   APP_DEBUG=false
   LOG_LEVEL=warning
   SESSION_DRIVER=file
   CACHE_STORE=file
   ```

   (`QUEUE_CONNECTION=database` оставить.) После смены `SESSION_DRIVER` участникам один раз придётся заново войти в кабинет: через кнопку «Кабинет» в боте это происходит само.

3. **После каждого обновления кода** (в `~/public_html/inspire`):

   ```bash
   /usr/local/bin/ea-php83 /usr/local/bin/composer install --no-dev --optimize-autoloader
   /usr/local/bin/ea-php83 artisan package:discover
   /usr/local/bin/ea-php83 artisan config:cache
   /usr/local/bin/ea-php83 artisan view:cache
   /usr/local/bin/ea-php83 artisan event:cache
   ```

   `route:cache` не нужен: часть маршрутов собирается пакетами, выигрыша почти нет.
   Если что-то странное, сбросить всё командой `artisan optimize:clear`.

4. **Блок для `public/.htaccess`** (на сервере, выше блока cPanel; в git файл не меняется, см. правило про `.htaccess`). Он включает сжатие и годовой кэш для файлов с `?v=` в адресе: такие адреса меняются при каждой замене файла, поэтому старого кэша бояться не нужно. Он заменяет прежний блок с `no-cache` для css и js (тот заставлял браузер проверять каждый файл при каждом заходе):

   ```apache
   <IfModule mod_deflate.c>
       AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json image/svg+xml text/plain
   </IfModule>

   <IfModule mod_headers.c>
       # файлы с ?v=… в адресе и сборка игры с хешем в имени можно хранить год
       <If "%{QUERY_STRING} =~ /(^|&)v=/ && %{REQUEST_URI} =~ m#\.(css|js|woff2|webp|png|jpg|jpeg|svg|ico|gif|pdf)$#">
           Header set Cache-Control "public, max-age=31536000, immutable"
       </If>
       <If "%{REQUEST_URI} =~ m#^/build/#">
           Header set Cache-Control "public, max-age=31536000, immutable"
       </If>
   </IfModule>
   ```

## Как проверить, где сайт ходит в базу

```bash
php tools/db-audit.php             # по каждой странице: число запросов к БД и время ответа
php tools/db-audit.php --queries   # то же, с текстом запросов
php tools/bench-request.php /photo # один «холодный» запрос в новом процессе (как на хостинге)
```

Публичные страницы должны показывать **0 запросов**. Кабинет: 1–2 на страницу (поиск участника по сессии и сами данные). Если где-то стало больше, смотрите `--queries`.

Замеры на этом компьютере (локальная база отвечает мгновенно; на хостинге каждый запрос к базе стоит заметно дороже, поэтому там выигрыш будет больше):

| | было | стало |
|---|---|---|
| Публичная страница, запросов к БД | 2–3 | 0 |
| Кабинет, запросов к БД | 3–4 | 1–2 |
| Вход в кабинет по токену, запросов | 15 | 2 |
| Холодный запрос к сайту (OPcache + кэши) | 120–150 мс | 75–100 мс |
