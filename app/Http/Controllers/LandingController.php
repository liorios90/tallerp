<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            $home = Auth::user()->role === 'sistemas' ? 'companies.index' : 'dashboard';

            return redirect()->route($home);
        }

        return view('landing', [
            'whatsappUrl' => $this->whatsappUrl(),
        ]);
    }

    private function whatsappUrl(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) config('services.workshop.whatsapp')) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '593'.substr($digits, 1);
        }

        $message = 'Hola, quiero un presupuesto para reparar mi equipo.';

        return 'https://web.whatsapp.com/send?phone='.$digits.'&text='.rawurlencode($message);
    }
}
