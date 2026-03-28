# REST API платформы "Стратегия успешного наставничества"

Эта документация описывает backend REST API, реализованный в `src`. Инструкции по запуску проекта находятся в корневом `README.md`.

## Архитектура API

Backend структурирован по принципам SOLID:

- Controllers отвечают только за HTTP-запросы и ответы.
- Services содержат бизнес-логику и инкапсулируют работу с моделями.
- Models описывают сущности базы данных и отношения между ними.
- `App\Providers\AppServiceProvider` связывает интерфейсы сервисов с их реализациями.

## Базовый URL

- Локальный API: `/api`
- Пример: `http://localhost/api/stages`

## Основные сущности

- `QuestStage` — этапы квеста
- `QuestTask` — задания этапов
- `TaskSubmission` — ответы участников на задания
- `Assessment` — оценки экспертов
- `StageProgress` — прогресс прохождения этапов

## Эндпойнты

### Стадии квеста

- `GET /api/stages`
  - Возвращает список всех этапов.

- `GET /api/stages/{stageId}`
  - Возвращает данные одного этапа с задачами.

- `GET /api/stages/{stageId}/tasks`
  - Возвращает задачи конкретного этапа.

### Задания

- `GET /api/tasks`
  - Список всех заданий с информацией об этапе.

- `GET /api/tasks/{taskId}`
  - Данные по конкретному заданию.

### Ответы участников

- `POST /api/submissions`
  - Создаёт новую отправку задания.
  - Тело запроса:

```json
{
  "quest_task_id": 1,
  "user_id": 2,
  "answer_text": "Ответ участника",
  "answer_data": {"steps": ["A", "B"]}
}
```

- `GET /api/users/{userId}/progress`
  - Возвращает прогресс пользователя по отправленным заданиям и оценкам.

### Оценки экспертов

- `POST /api/submissions/{submissionId}/assessments`
  - Создаёт оценку для отправки участника.
  - Тело запроса:

```json
{
  "evaluator_id": 3,
  "score": 18,
  "comment": "Отличная логика",
  "rubric": {"clarity": 5, "strategy": 4}
}
```

## Контроллеры

- `App\Http\Controllers\Api\StageController`
- `App\Http\Controllers\Api\TaskController`
- `App\Http\Controllers\Api\SubmissionController`
- `App\Http\Controllers\Api\AssessmentController`

Контроллеры используют сервисы и возвращают JSON-ответы.

## Сервисы

Интерфейсы и реализации:

- `App\Services\Interfaces\StageServiceInterface`
- `App\Services\Interfaces\TaskServiceInterface`
- `App\Services\Interfaces\SubmissionServiceInterface`
- `App\Services\Interfaces\AssessmentServiceInterface`

- `App\Services\StageService`
- `App\Services\TaskService`
- `App\Services\SubmissionService`
- `App\Services\AssessmentService`

## Файлы конфигурации

- `src/bootstrap/app.php` теперь подключает `routes/api.php`.
- `src/routes/api.php` содержит определение API-маршрутов.

## Пример запроса

```bash
curl -X POST http://localhost/api/submissions \
  -H 'Content-Type: application/json' \
  -d '{"quest_task_id":1,"user_id":2,"answer_text":"Ответ","answer_data":{"step":"A"}}'
```

## Примечания

- Все ответы API возвращаются в формате JSON.
- Валидация запросов выполняется в контроллерах через `Request::validate()`.
- Бизнес-логика остаётся в сервисах для удобства тестирования и поддержки.

## Swagger UI

API-документацию в формате OpenAPI можно просмотреть по адресу:

- `http://localhost/docs`

Swagger JSON доступен напрямую по адресу:

- `http://localhost/swagger.json`
