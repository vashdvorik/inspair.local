# Практические сценарии работы с Laravel Copilot Workspace

Эти схемы помогут выбрать агентов и шаги под типовые случаи. В каждом сценарии сначала убедитесь, что конфиги качества настроены (40-tools-init) и зависимости установлены (`composer install`).

## 1. Новый монолит/API на Laravel
- Требования: 10-requirements-create → 10-domain-describe → 20-architecture-plan → 20-batches-plan.
- Каркас: 30-laravel-app-create (если нет приложения) → настройка `.env`/`APP_KEY`.
- Реализация: 30-code-build по батчам.
- Проверки: 40-tests-run, при необходимости 40-code-review.
- Документация: 50-docs-update.
- Поставка: 60-release-build → 60-deploy-bundle-build.

## 2. Доработка существующего проекта
- Подключение инструментов: 40-tools-init (phpstan/pint/phpunit), проверка `.env`.
- Требования/батчи: 10/20-агенты или работа по текущему backlog’у.
- Код: 30-code-build, проверки 40-tests-run, ревью 40-code-review.
- Документация: 50-docs-update.
- Поставка: 60-release-build → 60-deploy-bundle-build.

## 3. Разработка пакета/библиотеки
- Инициализация: 30-laravel-app-create (режим package skeleton) или ручная настройка composer.
- Требования/архитектура: 10/20-агенты, описание публичных API/контрактов.
- Реализация: 30-code-build; тесты через orchestra/testbench.
- Проверки: 40-tests-run (phpstan/pint/phpunit), ревью 40-code-review.
- Документация и релиз: 50-docs-update → 60-release-build (zip/tar без `vendor`).

## 4. Подготовка к продакшен-деплою
- Убедитесь в наличии: `.env` с prod-настройками, кэши конфигов/роутов, миграции/сидеры.
- Запустите 40-tests-run (artisan test, phpstan, pint) и проверку миграций.
- Соберите фронтенд (vite/webpack) при необходимости.
- 60-release-build для слепка → 60-deploy-bundle-build (исключить `vendor`/`node_modules`, добавить инструкции деплоя).

## Памятка
- Всегда сверяйтесь с правилами в `rules/` (архитектура, безопасность, производительность, ops).
- Логи проверок и батчей складывайте в `results/checks/**` и `results/batches/**`.
- Фиксируйте изменения в документации через 50-docs-update.
