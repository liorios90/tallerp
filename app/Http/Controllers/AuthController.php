<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route($this->homeRoute());
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);
        $credentials['email'] = mb_strtolower(trim($credentials['email']));
        unset($credentials['remember']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'El correo o la contraseña no son correctos.'])->onlyInput('email');
        }

        $user = $request->user();
        if ($user->role === 'usuario' && ! $this->companyIsActive($user->company_id)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu empresa está inactiva. No puedes ingresar.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route($this->homeRoute());
    }

    private function homeRoute(): string
    {
        return Auth::user()?->role === 'sistemas' ? 'companies.index' : 'dashboard';
    }

    private function companyIsActive(mixed $companyId): bool
    {
        if (! $companyId) {
            return false;
        }

        return (int) DB::table('companies')->where('id', $companyId)->value('active') === 1;
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
