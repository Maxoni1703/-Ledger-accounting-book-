# Ledger

Это учебный проект по учёту хозяйственных операций с двойной записью. Я сделал простую систему, где можно вести счета, создавать транзакции и проверять, чтобы сумма дебета была равна сумме кредита.

Проект собирал как практическое задание по Laravel, MoonShine и PostgreSQL. В нём есть админка, базовый API и пример данных после миграции.

## Стек технологий

- Laravel 13
- PHP 8.3
- PostgreSQL 15
- MoonShine
- Laravel Sanctum
- Vite
- Docker Compose
- PHPUnit

## Установка и запуск

1. Клонируй репозиторий:

   git clone <url-репозитория>
   cd ledger

2. Установи зависимости PHP:

   composer install

3. Настрой окружение. В файле .env используй PostgreSQL:

   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5433
   DB_DATABASE=ledger
   DB_USERNAME=postgres
   DB_PASSWORD=secret

   В этом проекте Docker Compose уже поднимает PostgreSQL на порту 5433.

4. Подними базу через Docker:

   docker compose up -d

5. Прогон миграций и сидов:

   php artisan migrate --seed

   После этого в базе появятся примерные счета и транзакции, включая пользователя test@example.com с паролем password.

6. Создай пользователя для админки MoonShine:

   php artisan moonshine:user

7. Запусти приложение:

   php artisan serve

После этого открой:

http://localhost:8000

## Доступ к админке

Админка доступна по адресу:

http://localhost:8000/admin

Пользователя для входа создают командой:

php artisan moonshine:user

Если хочешь проверить вход с тестовыми данными, можно использовать пользователя из сидов:

- email: test@example.com
- password: password

Но для админки лучше создать отдельного пользователя через команду выше.

## Основные сущности

Account — это счёт в системе. Например, касса, расчётный счёт, выручка, расходы.

Transaction — это сама операция. У неё есть дата и описание, а также связанные проводки.

JournalEntry — это проводка внутри транзакции. Здесь уже есть дебет или кредит, сумма и связь со счётом.

## API

API работает через Laravel Sanctum. Сначала нужно получить токен через login, а потом использовать его в заголовке Authorization.

Пример входа:

curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

Пример ответа:

{
  "token": "...",
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com"
  }
}

Пример получения списка транзакций:

curl -X GET http://localhost:8000/api/transactions \
  -H "Authorization: Bearer <токен>"

Пример получения списка счетов:

curl -X GET http://localhost:8000/api/accounts \
  -H "Authorization: Bearer <токен>"

## Известные ограничения

- Это учебный проект, а не готовая бухгалтерская система для бизнеса.
- API пока базовый: есть login, logout, список счетов и транзакций.
- Проверка двойной записи есть в сервисе LedgerService, но интерфейс для сложных бухгалтерских сценариев ещё не доработан.
- Для работы с админкой нужен отдельный пользователь, который создаётся через MoonShine.
