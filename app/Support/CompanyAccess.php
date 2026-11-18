<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class CompanyAccess
{
    public static function denialMessage(?object $company): ?string
    {
        if ($company === null || (int) ($company->active ?? 0) !== 1) {
            return 'Tu empresa está inactiva. No puedes ingresar.';
        }

        if (($company->type ?? 'normal') !== 'demostracion') {
            return null;
        }

        $startsAt = self::date($company->starts_at ?? null) ?? self::date($company->registered_at ?? null);
        $days = (int) ($company->demo_days ?? 0);
        if ($startsAt === null || $days < 1) {
            return 'El periodo de demostración no está configurado. No puedes ingresar.';
        }

        $today = now()->startOfDay();
        if ($today->lt($startsAt)) {
            return 'Tu periodo de demostración comienza el '.$startsAt->format('d/m/Y').'.';
        }

        $endsAt = $startsAt->copy()->addDays($days);
        if ($today->gte($endsAt)) {
            return 'Tu periodo de demostración finalizó el '.$endsAt->format('d/m/Y').'. No puedes ingresar.';
        }

        return null;
    }

    public static function periodLabel(object $company): string
    {
        $startsAt = self::date($company->starts_at ?? null);
        $days = (int) ($company->demo_days ?? 0);
        if ($startsAt === null || $days < 1) {
            return 'Sin periodo configurado';
        }

        $endsAt = $startsAt->copy()->addDays($days);
        $today = now()->startOfDay();
        if ($today->lt($startsAt)) {
            return 'Inicia el '.$startsAt->format('d/m/Y').' · '.$days.' días';
        }

        if ($today->gte($endsAt)) {
            return 'Vencida el '.$endsAt->format('d/m/Y');
        }

        return $days.' días · vence el '.$endsAt->format('d/m/Y');
    }

    private static function date(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->startOfDay();
    }
}
