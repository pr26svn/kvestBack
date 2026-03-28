# Онлайн-платформа квеста "Стратегия успешного наставничества"

Этот проект реализует backend-часть образовательной платформы для конкурсного квеста кураторов учебных групп. Основной стек:

- Laravel 10
- MySQL в Docker
- Docker Compose

## Структура проекта

Основные директории:

- `app/` — приложение Laravel
- `database/migrations/` — миграции базы данных
- `database/seeders/` — сидеры для начальных данных
- `public/` — публичная точка входа
- `resources/` — frontend-ресурсы Laravel
- `front/` — Vue-фронтенд web-интерфейса

## Frontend

Каталог `front/` содержит Vue приложение для web-интерфейса. После поднятия backend можно запустить фронтенд из `front/`.

Фронтенд требует Node.js >= 16.8.0.

```bash
cd front
npm install
npm run dev
```

## Быстрый запуск

В этом репозитории используется Docker Compose из корневой директории проекта.

### 1. Скопируйте `.env`

```bash
cd src
cp .env.example .env
```

### 2. Настройте параметры базы данных

В `.env` установите следующие значения:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=students
DB_USERNAME=user
DB_PASSWORD=password
```

Если вы хотите использовать SQLite для локальной разработки, оставьте `DB_CONNECTION=sqlite`.

### 3. Запустите Docker

Вернитесь в корень проекта и поднимите контейнеры:

```bash
cd /home/pr26svn/projects/prof
docker compose up -d
```

### 4. Установите зависимости

Если зависимости ещё не установлены внутри контейнера:

```bash
docker compose exec app composer install
```

### 5. Сгенерируйте ключ приложения

```bash
docker compose exec app php artisan key:generate
```

### 6. Выполните миграции и сиды

```bash
docker compose exec app php artisan migrate --seed
```

Если хотите выполнить только миграции без сидов:

```bash
docker compose exec app php artisan migrate
```

## Полезные команды

Рабочая директория: `src`

- `docker compose exec app php artisan migrate`
- `docker compose exec app php artisan migrate --seed`
- `docker compose exec app php artisan db:seed`
- `docker compose exec app php artisan migrate:refresh --seed`
- `docker compose exec app php artisan config:cache`
- `docker compose exec app php artisan route:cache`
- `docker compose exec app php artisan view:clear`
- `docker compose exec app ./vendor/bin/phpunit`
- `docker compose exec app php artisan tinker`

Если вы работаете без Docker:

```bash
cd src
composer install
php artisan key:generate
php artisan migrate --seed
```

## Что уже добавлено

В проекте реализованы миграции для следующих сущностей:

- `users` — расширенный профиль пользователя
- `roles` и `role_user` — роли администратора, куратора и эксперта
- `badges` и `badge_user` — бейджи за достижения
- `quest_stages` и `quest_tasks` — этапы и задания квеста
- `stage_progress`, `task_submissions`, `assessments` — прогресс, ответы участников и оценки экспертов

## Настройка Git и GitHub

Если репозиторий ещё не инициализирован, выполните:

```bash
git init
git add .
git commit -m "Initial platform setup: migrations and seeders"
git branch -M main
git remote add origin <REMOTE_URL>
git push -u origin main
```

Если репозиторий уже инициализирован:

```bash
git status
git add .
git commit -m "Add project README, database schema and seeders"
git push
```

## Примечание

Если Docker-контейнеры уже запущены, но приложение не работает после изменений, перезапустите их:

```bash
docker compose restart
```
