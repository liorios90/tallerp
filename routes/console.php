<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('workshop:user-create', function (): int {
    $name = trim((string) $this->ask('Nombre del usuario'));
    $email = mb_strtolower(trim((string) $this->ask('Correo para iniciar sesión')));

    if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Ingresa un nombre y un correo válidos.');

        return Command::FAILURE;
    }

    if (User::query()->where('email', $email)->exists()) {
        $this->error('Ya existe un usuario con ese correo.');

        return Command::FAILURE;
    }

    $role = (string) $this->choice('Rol', ['sistemas', 'usuario'], 0);
    $companyId = null;

    if ($role === 'usuario') {
        $companies = DB::table('companies')->orderBy('first_name')->get();
        if ($companies->isEmpty()) {
            $this->error('No hay empresas. Crea primero un usuario de sistemas y registra la empresa desde el sistema.');

            return Command::FAILURE;
        }

        $labels = $companies->map(fn (object $company): string => $company->id.' · '.$company->first_name.' '.$company->last_name)->all();
        $companyId = (int) strtok((string) $this->choice('Empresa', $labels), ' ');
    }

    $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
    $confirmation = (string) $this->secret('Confirma la contraseña');

    if (mb_strlen($password) < 12 || $password !== $confirmation) {
        $this->error('La contraseña debe tener al menos 12 caracteres y coincidir con la confirmación.');

        return Command::FAILURE;
    }

    User::query()->create([
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'role' => $role,
        'company_id' => $companyId,
    ]);
    $this->info($role === 'sistemas' ? 'Usuario de sistemas creado. Solo puede registrar empresas.' : 'Usuario de empresa creado.');

    return Command::SUCCESS;
})->purpose('Create a user who can sign in to the workshop');
