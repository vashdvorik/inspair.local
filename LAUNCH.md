# Чек-лист запуска (сервер, cPanel)

Команды выполняются в `~/public_html/inspire`; PHP на сайте ea-php84.

## 1. Выкладка кода
```bash
git pull
/usr/local/bin/ea-php84 /usr/local/bin/composer install --no-dev --optimize-autoloader
/usr/local/bin/ea-php84 artisan migrate --force
/usr/local/bin/ea-php84 artisan optimize:clear
/usr/local/bin/ea-php84 artisan config:cache && /usr/local/bin/ea-php84 artisan view:cache && /usr/local/bin/ea-php84 artisan event:cache
```
`migrate` создаёт таблицы рассылок, модерации, возможностей и настроек, переносит 15 возможностей из `opportunities.json` и переводит участников на новую схему доступа (ожидавшие становятся обычными, отклонённые получают теневой бан).

## 2. Файл `.env` на сервере
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://inspire.education.md
LOG_LEVEL=warning
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database
TELEGRAM_TOKEN=…            # токен бота
TELEGRAM_ADMIN_IDS=…        # Telegram ID администраторов через запятую (кому слать предупреждения модерации)
```
Ключ OpenRouter в `.env` писать не нужно: он вводится в админке («ИИ»).

## 3. Cron (один, раз в минуту)
```
* * * * * cd /home/tirastyl/public_html/inspire && /usr/local/bin/ea-php84 artisan schedule:run >> /dev/null 2>&1
```
Через него работают: очередь (эмбеддинги), рассылки админа (каждую минуту), модерация текстов (5 минут), рассылка новых возможностей (15 минут), пополнение вопросов игры (30 минут). **Без этого cron ничего из перечисленного не запустится.**

## 4. Админка
1. Войти в `/admin`, создать себе администратора (пользователи из `users`; регистрации в админке нет).
2. «ИИ»: вставить ключ OpenRouter, выбрать модели (`openai/gpt-4o-mini`, запасная `google/gemma-4-31b-it`; у эмбеддингов запасную не ставить), нажать «Проверить».
3. «Участники»: отметить организаторов как «Модератор / организатор», если им нужны предупреждения модерации в Telegram.

## 5. Бот
- Вебхук должен быть настроен на `https://inspire.education.md/telegram/webhook`. Проверка: `https://api.telegram.org/bot<ТОКЕН>/getWebhookInfo` (в ответе `last_error_message` должен быть пустым).
- Пройти регистрацию со своего аккаунта: приветствие, два вопроса, сообщение после регистрации, кнопка «Открыть кабинет».

## 6. PHP и веб-сервер
- OPcache включён (MultiPHP INI Editor, см. `PERFORMANCE.md`).
- Блок сжатия и кэша для `public/.htaccess` (в `PERFORMANCE.md`, раздел 4). Сам файл в git не менять.

## 7. Игра
Игра выключена по умолчанию (режим «Выключена»). Перед стартом: «Игра «Угадай, кто это»» → «Запущена». Вопросы начнут создаваться по расписанию, когда участников с заполненным профилем не меньше 3.

## 8. Рассылка новых возможностей
Первый запуск `opportunities:notify` ничего не рассылает, он только отмечает уже существующие 15 возможностей. Если нужно разослать и их, один раз: `artisan opportunities:notify --all`.

## 9. Проверка после запуска
- Главная, «Хакатон», «Возможности», «Партнёры» открываются; кабинет открывается из бота; язык переключается EN/RU/RO.
- В админке «ИИ» нет оранжевого предупреждения про ключ; «Проверить» даёт зелёный ответ.
- «Рассылки» → «Тест себе» доходит до Telegram.
- Через час в `storage/logs/laravel.log` нет новых ошибок.
