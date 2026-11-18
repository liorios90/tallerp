<?php

namespace App\Http\Middleware;

use App\Support\CompanyAccess;
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
        $company = $user?->company_id
            ? DB::table('companies')->where('id', $user->company_id)->first()
            : null;
        $denial = CompanyAccess::denialMessage($company);

        if ($denial !== null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => $denial,
            ]);
        }

        return $next($request);
    }
}
