# Документация To-Do List API

## Содержание
1. Требования
2. Установка
3. Конфигурация
4. Эндпоинты
   - Аутентификация
   - Задачи
5. Примеры запросов
6. Коды ошибок

## Требования

- PHP 8.1+
- PostgreSQL 12+ или MySQL 8+
- Composer 2.0+
- Apache/Nginx

## Установка
```
git clone https://github.com/german142000/vcgroup.git
cd todo-api
composer install
cp .env.example .env
```
в `postgresql` сервере необходимо создать базу и выполнить в ней код из `database/schema.sql`:
```
\i database/schema.sql
```

## Конфигурация

Настройте .env файл:
```
APP_ENV=development
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_NAME=...
DB_USER=...  # или ваш пользователь
DB_PASSWORD=...  # пароль, который вы задали
DB_TIMEOUT=5

JWT_SECRET=...
```
JWT_SECRET=your_random_secret_key  

Инициализация БД:
```
psql -U db_user -d todo_db -f database/schema.sql  
```
## Эндпоинты

### Аутентификация

| Метод | Эндпоинт         | Описание               |
|-------|------------------|------------------------|
| POST  | /api/register     | Регистрация            |
| POST  | /api/login        | Авторизация            |
| POST  | /api/logout       | Выход из системы       |

### Задачи

| Метод | Эндпоинт               | Описание              |
|-------|------------------------|-----------------------|
| GET   | /api/tasks             | Список задач          |
| POST  | /api/tasks             | Создание задачи       |
| GET   | /api/tasks/{id}       | Просмотр задачи       |
| PUT   | /api/tasks/{id}       | Обновление задачи     |
| DELETE| /api/tasks/{id}       | Удаление задачи       |

## Примеры запросов

Регистрация
```
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```
Авторизация
```
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```
Выход
```
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer eyJhbGciOiJ..."
```
Создание задачи
```
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer eyJhbGciOiJ..." \
  -H "Content-Type: application/json" \
  -d '{"title":"Новая задача","description":"Описание"}'
```
Получение задач с фильтрами
```
curl -X GET "http://localhost:8000/api/tasks?status=в_работе&page=1" \
  -H "Authorization: Bearer eyJhbGciOiJ..."
```
Получение задачи
```
curl -X GET "http://localhost:8000/api/tasks/number" \
  -H "Authorization: Bearer eyJhbGciOiJ..."
```
Удаление задачи
```
curl -X DELETE "http://localhost:8000/api/tasks/number" \
  -H "Authorization: Bearer eyJhbGciOiJ..."
```

Обновление задачи
```
curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer eyJhbGciOiJ..." \
  -H "Content-Type: application/json" \
  -d '{"status": "завершено"}'
```
## Коды ошибок

| Код  | Описание                     |
|------|------------------------------|
| 400  | Неверный запрос              |
| 401  | Не авторизован               |
| 403  | Доступ запрещен              |
| 404  | Не найдено                   |
| 422  | Ошибка валидации            |
| 500  | Внутренняя ошибка сервера    |

Пример ошибки:
```
{
  "success": false,
  "error": "Invalid credentials",
  "errors": {
    "email": ["The email field is required"]
  }
}
```
## Запуск сервера
```
php -S 0.0.0.0:8000 -t public
```
При развёртывании сервера apache\nginx нужно указать public корневой директорией

## Лицензия

Разрешается изучать и проверять код, но запрещается его использовать в коммерческих продуктах.