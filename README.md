INSTALL
=========

1. Clone
2. Create .env file
3. RUN `composer install`
4. RUN `php bin/console doctrine:database:create`
5. RUN `php bin/console doctrine:migrations:migrate`
6. RUN `php bin/console doctrine:fixtures:load`

## RUN SERVER
`php bin/console server:run`

## Credentials

#####Super Admin:
**email:** `admin@gmail.com`
**password:** `admin`

#####Agent:
**email:** `agent@gmail.com`
**password:** `agent`

#####Tenant:
**email:** `tenant@gmail.com`
**password:** `tenant`
