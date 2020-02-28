Как запустить бекенд
=========

1. Клонировать репозиторий
2. Создать файл .env скопировать содержимое .env_example заменить параметры {PARAMS}
3. Запустить `composer install`
4. Запустить `php bin/console doctrine:database:create`
5. Запустить `php bin/console doctrine:migrations:migrate`
6. Запустить `php bin/console doctrine:fixtures:load`

## Запустить сервер
`php bin/console server:run`

## Доступы
#####Super Admin:
**email:** `admin@gmail.com`
**password:** `admin`
#####Agent:
**email:** `agent@gmail.com`
**password:** `agent`
#####Tenant:
**email:** `tenant@gmail.com`
**password:** `tenant`
