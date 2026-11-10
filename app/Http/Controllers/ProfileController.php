<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('workshop')->with('page', 'profile');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'Ese correo ya está registrado.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);
        $data['email'] = mb_strtolower(trim($data['email']));

        DB::transaction(function () use ($data, $user): void {
            $attributes = ['email' => $data['email']];
            if (! empty($data['password'])) {
                $attributes['password'] = $data['password'];
            }

            $user->update($attributes);

            if ($user->company_id) {
                DB::table('companies')->where('id', $user->company_id)->update([
                    'email' => $data['email'],
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Perfil actualizado.');
    }
}
