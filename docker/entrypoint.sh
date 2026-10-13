#!/bin/sh
set -e

# El esquema es de las migraciones (ADR-001): la base arranca vacía y se migra al iniciar.
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=8000
