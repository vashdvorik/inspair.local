# Copilot: короткая инструкция для ИИ

Этот репозиторий использует набор специализированных агентов с именами вида `10-*`, `20-*`, `30-*`, `40-*`, `50-*`, `60-*`; подробное описание ролей и цепочки 10→60 см. в разделе «Роли агентов» корневого `README.md`.

## Контекст
- Проект: Laravel-приложение (PHP 8.1+). Основные каталоги — `app/`, `routes/`, `config/`, `database/`, `resources/`, `tests/`.
- Источник требований: каталог `inputs/` (используйте все файлы, включая `inputs/README.md`).
- Правила и стандарты разработки: `rules/README.md` и все файлы в `rules/` — учитывай их перед генерацией ТЗ, архитектуры и кода.
- Дополнительные правила и процессы: `README.md` (корень) и `inputs/README.md`.
- Архитектурные решения и принципы фиксируются в `docs/README.md` и отдельных документах в `docs/guides/**`.
- **Коммуникация**: все ответы и отчёты пиши на русском языке, включая handoff'ы и комментарии к изменениям.

## Основные требования к коду
- Соблюдай слои и зависимости: доменные сервисы не зависят напрямую от транспорта (HTTP/Console) и инфраструктуры (Eloquent/Redis/External API) без портов и абстракций.
- Используй валидацию через Form Requests или кастомные валидаторы; авторизацию через Policies/Gates/Permissions.
- Все публичные входы проходят проверку аутентификации/авторизации и rate limiting; чувствительные операции — в транзакциях.
- Используй сервис-контейнер, события/слушатели и очереди там, где это оправдано; не дублируй бизнес-логику в контроллерах.
- Миграции и сидеры идемпотентны, покрывают индексы и ограничения; Eloquent связи описаны явно, избегай N+1 (eager loading по умолчанию).
- Локализация — через `lang/**` и `__()`/Blade-директивы; никаких строк в логике.
- PHP-файлы начинаются с `declare(strict_types=1);`, соблюдаем PSR-12 или Pint-пресет.

## Рабочие каталоги агентов
- Обязательный вход: всё содержимое `inputs/` (включая `inputs/README.md`).
- Для планирования используйте `inputs/batches/` (`pipeline.json`, `{id}.yaml`, вспомогательные заметки).
- Для артефактов и логов используйте `results/`: `checks/` для отчётов проверок и `batches/` для журналов выполнения.
- Складывайте вспомогательные скрипты в `bin/` (опишите их в README).
- Если нужны дополнительные данные, создавайте соседние каталоги прямо в корне и документируйте их назначения в `README.md`.

- PHPUnit/Artisan: `php artisan test --log-junit results/checks/phpunit-junit.xml`.
- PHPStan/Larastan: `vendor/bin/phpstan analyse -c phpstan.neon --error-format=json > results/checks/phpstan-report.json`.
- Pint или PHP-CS-Fixer: `vendor/bin/pint --test > results/checks/pint-report.txt` или `vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff > results/checks/phpcs-report.txt`.
- Deptrac (опционально): `vendor/bin/deptrac analyse --no-interaction > results/checks/deptrac-report.txt`.

## Роли агентов
- **20-batches-plan** (`.github/agents/20-batches-plan.agent.md`): работает с `inputs/**`, ведёт `inputs/batches/pipeline.json`, описывает батчи в `inputs/batches/{id}.yaml` и фиксирует DoD. Перед планированием проходит чеклист: версия PHP/Node, наличие `composer`, наличие корневых конфигов (`phpunit`, `phpstan.neon`, `.php-cs-fixer.dist.php`/`pint.json`), состояние `app/`, `routes/`, миграций и стандартов. Пробелы описываются отдельными батчами или рисками.
- **30-laravel-app-create** (`.github/agents/30-laravel-app-create.agent.md`): создаёт каркас Laravel-приложения или пакета, инициализирует `.env`, `APP_KEY`, базовые каталоги и опционально подключает Sail/Docker.
- **30-code-build** (`.github/agents/30-code-build.agent.md`): берёт первый pending-батч, вносит изменения в код, запускает проверки и обновляет отчёты и статусы. Следит за валидацией (FormRequests), авторизацией (Policies/Gates), транзакциями, N+1, кэшем, логированием, конфигами/ENV, безопасным Blade/escaping.
- **40-tools-init** (`.github/agents/40-tools-init.agent.md`): определяет актуальный проект, уточняет контекст и готовит конфиги статических проверок (PHPStan/Larastan, Deptrac при необходимости, PHPUnit, Pint/CS Fixer) под текущий namespace и пути.
- **50-docs-update** (`.github/agents/50-docs-update.agent.md`): читает `results/batches/*.md`, требования и код, обновляет `docs/**` (ADR, спецификации, cookbook, OPS). Не придумывает данные: если информации не хватает, формирует список вопросов или предлагает агентам групп `10-*`/`20-*` новый батч.
- **60-release-build** (`.github/agents/60-release-build.agent.md`): собирает релизный пакет, проверяет коммиты, формирует каталог `packages/releases/<start>-<end>` с изменёнными файлами и README, может подсказать последние коммиты.
- **60-deploy-bundle-build** (`.github/agents/60-deploy-bundle-build.agent.md`): принимает готовый релиз и собирает деплой-пакет (zip/tar или контейнерные артефакты) в `packages/deploy/<version>/`, без правок исходников.

## Быстрые ссылки
- Архитектура и процессы: `docs/README.md` (расширяйте по мере появления отдельных документов и фиксируйте ссылки здесь же).
- Ошибки, DSL и cookbook: заведите файлы в `docs/` и обновляйте README, как только появится контент.
- Примеры данных: `examples/` (коллекции запросов, сниппеты, demo). Создавайте подпапки и описывайте их назначение в `examples/README.md`.

Документируйте новые папки и процессы прямо в корневом `README.md` (при необходимости добавляйте отдельные файлы в `inputs/`). Так любой агент поймёт технологию за пару минут.
