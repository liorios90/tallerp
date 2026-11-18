<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\CompanyAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:40'],
            'phone_secondary' => ['nullable', 'string', 'max:40'],
            'document' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'email.unique' => 'Ese correo ya está registrado.',
        ]);

        $data['email'] = mb_strtolower(trim($data['email']));
        $today = now()->toDateString();

        $user = DB::transaction(function () use ($data, $today): User {
            $companyId = DB::table('companies')->insertGetId([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'phone_secondary' => $data['phone_secondary'] ?? null,
                'document' => trim($data['document']),
                'email' => $data['email'],
                'address' => $data['address'] ?? null,
                'active' => true,
                'type' => 'demostracion',
                'demo_days' => 10,
                'registered_at' => $today,
                'starts_at' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return User::query()->create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'usuario',
                'company_id' => $companyId,
            ]);
        });

        $company = DB::table('companies')->where('id', $user->company_id)->first();
        $denial = CompanyAccess::denialMessage($company);
        if ($denial !== null) {
            return redirect()->route('login')->withErrors(['email' => $denial])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
