# Laravel Copilot Workspace

Этот репозиторий — рабочая площадка для разработки Laravel-проектов силами GitHub Copilot и специализированных агентов. Он объединяет требования, архитектуру, тестовые данные, артефакты поставки и инструкции, чтобы можно было поднять новое приложение, собрать релиз и вести документацию без переключения в другие хранилища.

## Роли агентов

- **00-help-assistant** — проводник по Workspace: отвечает на вопросы о структуре проекта, выборе агентов и безопасных изменениях в документации и `.agent.md`.
- **10-requirements-create** — из сырых идей и черновых ТЗ формирует структурированные требования в `inputs/requirements/**`, при необходимости обновляя `inputs/README.md`.
- **10-domain-describe** — описывает предметную область по актуальным требованиям, поддерживает глоссарий, сущности и бизнес-правила в `inputs/domain/**`.
- **20-architecture-plan** — на основе требований и домена готовит архитектурный план, наполняет `inputs/design/**` (и при необходимости `docs/architecture/**`).
- **20-batches-plan** — планирует работу батчами, ведёт `inputs/batches/pipeline.json` и YAML-батчи.
- **30-laravel-app-create** — готовит каркас Laravel-приложения (composer create-project, `.env`, ключ приложения) или пакет, если проект стартует с нуля.
- **30-code-build** — реализует выбранные батчи: меняет код в `app/**`/`routes/**`/`resources/**`, запускает проверки и пишет логи в `results/batches/**` и `results/checks/**`.
- **40-tools-init** — настраивает конфиги статических проверок (PHPStan/Larastan, Pint или PHP-CS-Fixer, PHPUnit) под текущий проект, чтобы их можно было запускать без ручных правок.
- **40-tests-run** — запускает PHPUnit/Artisan test, PHPStan, Pint/CS Fixer и другие анализаторы, складывая отчёты в `results/checks/**`.
- **40-code-review** — проводит код-ревью изменений в `app/**` и сопутствующих каталогах, оформляет замечания в `results/reviews/**`.
- **50-docs-update** — синхронизирует реализацию с документацией, обновляет `docs/**`, ADR, cookbook и сопутствующие материалы.
- **60-release-build** — собирает релизные артефакты по диапазону коммитов в `packages/releases/<start>-<end>/` и добавляет README.
- **60-deploy-bundle-build** — формирует пакет для деплоя (zip/tar или контейнерный контент) на основе релиза в `packages/deploy/<version>/`.

## Рабочий процесс

1. **С чего начать?** Сформулируйте задачу и положите черновик в `inputs/` (хотя бы текстовый файл). Если непонятно, что делать — спросите у **00-help-assistant**.
2. **Оформить требования.** Запустите **10-requirements-create** — он превратит черновики в структурированные требования в `inputs/requirements/**`.
3. **Понять предметную область.** Запустите **10-domain-describe**, чтобы агент выписал глоссарий, сущности и бизнес-правила в `inputs/domain/**`.
4. **Продумать архитектуру.** Передайте собранные требования и домен агенту **20-architecture-plan** — он набросает архитектурный план в `inputs/design/**` (и при необходимости добавит `docs/architecture/**`).
5. **Разбить работу на шаги.** Запустите **20-batches-plan** — агент составит список батчей в `inputs/batches/**` и подскажет DoD для каждого.
6. **Нужен каркас приложения?** Если кода нет, вызовите **30-laravel-app-create**: он создаст минимальную структуру Laravel (composer create-project или scaffold пакета), подготовит `.env` и ключ.
7. **Подготовить инструменты.** Запустите **40-tools-init** — он проверит `composer.json`, `phpstan.neon`, `deptrac.yaml`, `.php-cs-fixer.dist.php`/`pint.json`, `phpunit.xml` и настроит их под текущий namespace и пути.
8. **Реализация.** Запускайте **30-code-build**: берёт первый pending-батч, правит код, запускает проверки и записывает отчёт в `results/**`.
9. **Проверки по требованию.** **40-tests-run** гоняет `php artisan test`, PHPStan/Larastan, Pint/CS Fixer и складывает логи в `results/checks/**`.
10. **Ревью.** **40-code-review** делает код-ревью и сохраняет замечания в `results/reviews/**`.
11. **Документация.** **50-docs-update** синхронизирует сделанное с `docs/**`, чтобы знания не терялись.
12. **Поставка.** **60-release-build** собирает релиз в `packages/releases/<range>/`, а **60-deploy-bundle-build** — пакет деплоя (zip/tar/контейнер) в `packages/deploy/<version>/`.

## Быстрый старт под свой проект

1. Склонируйте репозиторий и выполните `composer install` (для агентов и утилит).
2. Если проекта ещё нет — запустите **30-laravel-app-create**, он выполнит `composer create-project laravel/laravel <dir>` и создаст `.env`/`APP_KEY`.
3. Если проект уже есть — убедитесь, что `composer.json`, `phpstan.neon`, `deptrac.yaml`, `phpunit.xml`, `.php-cs-fixer.dist.php` (или `pint.json`) указывают на `app/`, `routes/`, `database/`, `tests/`; скорректируйте через **40-tools-init**.
4. Заполните черновые требования в `inputs/requirements/` и прогоните цепочку агентов 10→20 для планирования.
5. Запускайте **30-code-build** для реализации батчей, **40-tests-run** для проверок и следуйте остальным шагам рабочего процесса.

## Структура репозитория

| Каталог | Назначение |
| --- | --- |
| `.github/` | Инструкции агентов и вспомогательные материалы Copilot. |
| `rules/` | Обязательные правила разработки под Laravel (`rules/README.md`). |
| `docs/` | Архитектура, ADR, гайды (`docs/README.md`, `docs/guides/*`). |
| `examples/` | Демоматериалы, коллекции запросов, сниппеты (`examples/README.md`). |
| `inputs/` | Требования, домен, дизайн, батчи, промпты (`inputs/README.md`). |
| `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `tests/` | Каркас Laravel-приложения (может быть заполнен агентами). |
| `packages/` | Релизы и деплой-пакеты (`packages/README.md`, `packages/releases/README.md`). |
| `results/` | Отчёты проверок и батчей (`results/README.md`). |
| `vendor/` | Зависимости Composer (Laravel, phpunit, larastan, pint и т.д.). |

### Где читать подробнее
- `docs/README.md` — список постоянных документов и гайдов.
- `docs/guides/copilot-laravel-overview.md` — обзор workspace и цепочки агентов под Laravel.
- `inputs/README.md` — как устроены требования/домен/батчи.
- `examples/README.md` — правила и структура примеров/снапшотов.
- `packages/README.md` и `packages/releases/README.md` — как складывать релизы/деплой-пакеты.
- `results/README.md` — куда сохранять логи проверок и отчёты батчей.

## Полезные команды

```bash
composer install
php artisan key:generate
php artisan test --testsuite=Feature --testsuite=Unit --log-junit results/checks/phpunit-junit.xml
vendor/bin/phpstan analyse -c phpstan.neon
vendor/bin/pint --test # или php-cs-fixer fix --dry-run --diff
```

Команды выше запускаются из корня репозитория. Результаты статических анализаторов и тестов можно складывать в `results/checks/` для последующего анализа агентами `20-batches-plan`, `30-code-build`, `40-tests-run` и `50-docs-update`.
