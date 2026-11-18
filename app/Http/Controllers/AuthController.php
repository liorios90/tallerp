<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\CompanyAccess;
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
        if ($user->role === 'usuario') {
            $company = $user->company_id
                ? DB::table('companies')->where('id', $user->company_id)->first()
                : null;
            $denial = CompanyAccess::denialMessage($company);
            if ($denial !== null) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => $denial,
                ])->onlyInput('email');
            }
        }

        $request->session()->regenerate();

        return redirect()->route($this->homeRoute());
    }

    private function homeRoute(): string
    {
        return Auth::user()?->role === 'sistemas' ? 'companies.index' : 'dashboard';
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
