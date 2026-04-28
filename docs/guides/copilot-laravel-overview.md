# Copilot Laravel Workspace: краткий обзор

## Цель
Рабочая среда для разработки Laravel-проектов силами GitHub Copilot и специализированных агентов. Все артефакты — требования, домен, архитектура, батчи, код, проверки, релизы и деплой — ведутся в одном репозитории.

## Цепочка агентов 10→60
- **00-help-assistant** — отвечает на вопросы о структуре, ролях, безопасных изменениях в `.agent.md`.
- **10-requirements-create** — приводит сырые идеи к структурированным требованиям (`inputs/requirements/**`).
- **10-domain-describe** — оформляет глоссарий, сущности и бизнес-правила (`inputs/domain/**`).
- **20-architecture-plan** — описывает слои/компоненты и потоки данных (`inputs/design/**`, при необходимости `docs/architecture/**`).
- **20-batches-plan** — строит `inputs/batches/pipeline.json` и YAML-батчи.
- **30-laravel-app-create** — поднимает каркас Laravel (composer create-project, `.env`, `APP_KEY`) или пакет.
- **30-code-build** — реализует батчи, правит код, запускает проверки, пишет отчёты в `results/**`.
- **40-tools-init** — настраивает phpstan/larastan, pint/фиксер, phpunit (и опционально deptrac) под проект.
- **40-tests-run** — гоняет artisan test, phpstan, pint/фиксер (dry-run), сохраняет логи в `results/checks/**`.
- **40-code-review** — ревью кода, формирует замечания в `results/reviews/**`.
- **50-docs-update** — синхронизирует реализацию с `docs/**` (ADR, spec, cookbook, ops).
- **60-release-build** — собирает релизный слепок в `packages/releases/<start>-<end>/` с README.
- **60-deploy-bundle-build** — делает деплой-пакет (zip/tar/контейнер) из релиза в `packages/deploy/<version>/`.

## Рабочие каталоги
- `inputs/` — требования, домен, дизайн, батчи, промпты.
- `docs/` — постоянная документация, гайды, ADR, cookbook, ops.
- `app/`, `routes/`, `database/`, `config/`, `resources/`, `tests/` — код и тесты.
- `results/` — логи проверок и батчей.
- `packages/` — релизы и деплой-пакеты.

## Минимальные команды качества
```bash
composer install
php artisan key:generate
php artisan test --log-junit results/checks/phpunit-junit.xml
vendor/bin/phpstan analyse -c phpstan.neon --error-format=json > results/checks/phpstan-report.json
vendor/bin/pint --test > results/checks/pint-report.txt
```

## Правила (кратко)
- Валидация через FormRequest/Validator, авторизация через Policies/Gates.
- Никакого хардкода секретов; конфиги из `.env`/`config/**`.
- Тонкие контроллеры, бизнес-логика в сервисах/домене; транзакции для связанных операций.
- Избегай N+1: используй eager loading, индексы в миграциях.
- Локализация через `lang/**` и `__()`/Blade, безопасное экранирование.
- Соблюдай PSR-12/Pint-пресет, запускай статику и тесты перед handoff.
