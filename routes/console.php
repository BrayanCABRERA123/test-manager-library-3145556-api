<?php

use App\Application\Services\BootstrapAdminService;
use Illuminate\Support\Facades\Artisan;

Artisan::command('library:bootstrap-admin', function (BootstrapAdminService $service) {
    $username = (string) env('ADMIN_USERNAME');
    $password = (string) env('ADMIN_PASSWORD');

    if ($username === '' || $password === '') {
        $this->error('Faltan ADMIN_USERNAME o ADMIN_PASSWORD en el entorno.');

        return 1;
    }

    $this->info($service->ensureAdmin($username, $password)
        ? 'Administrador inicial creado.'
        : 'El administrador inicial ya existe.');

    return 0;
})->purpose('Crea el administrador inicial desde el entorno (D-10)');
