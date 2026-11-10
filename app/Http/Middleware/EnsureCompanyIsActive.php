<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $active = $user?->company_id
            && (int) DB::table('companies')->where('id', $user->company_id)->value('active') === 1;

        if (! $active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu empresa está inactiva. No puedes ingresar.',
            ]);
        }

        return $next($request);
    }
}
