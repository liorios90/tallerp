<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;
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

    $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
    $confirmation = (string) $this->secret('Confirma la contraseña');

    if (mb_strlen($password) < 12 || $password !== $confirmation) {
        $this->error('La contraseña debe tener al menos 12 caracteres y coincidir con la confirmación.');

        return Command::FAILURE;
    }

    User::query()->create(['name' => $name, 'email' => $email, 'password' => $password]);
    $this->info('Usuario creado. Ya puede iniciar sesión en el taller.');

    return Command::SUCCESS;
})->purpose('Create a user who can sign in to the workshop');
