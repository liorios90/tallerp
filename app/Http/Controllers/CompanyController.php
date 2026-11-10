<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = DB::table('companies')->orderByDesc('id')->get();

        return view('workshop', compact('companies'))->with('page', 'companies');
    }

    public function create(): View
    {
        return view('workshop', ['company' => null, 'companyUser' => null])->with('page', 'company_form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCompany($request);

        DB::transaction(function () use ($data): void {
            $companyId = DB::table('companies')->insertGetId($this->companyAttributes($data));
            User::query()->create([
                'name' => $this->fullName($data),
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'usuario',
                'company_id' => $companyId,
            ]);
        });

        return redirect()->route('companies.index')->with('success', 'Empresa creada. Ya puede ingresar con su correo y contraseña.');
    }

    public function edit(int $company): View
    {
        $record = DB::table('companies')->where('id', $company)->first();
        abort_unless($record, 404);
        $companyUser = DB::table('users')->where('company_id', $company)->where('role', 'usuario')->first();

        return view('workshop', ['company' => $record, 'companyUser' => $companyUser])->with('page', 'company_form');
    }

    public function update(Request $request, int $company): RedirectResponse
    {
        $record = DB::table('companies')->where('id', $company)->first();
        abort_unless($record, 404);
        $companyUser = DB::table('users')->where('company_id', $company)->where('role', 'usuario')->first();
        abort_unless($companyUser, 404);

        $data = $this->validateCompany($request, $companyUser->id, false);

        DB::transaction(function () use ($data, $company, $companyUser): void {
            DB::table('companies')->where('id', $company)->update($this->companyAttributes($data, false));
            $userAttributes = [
                'name' => $this->fullName($data),
                'email' => $data['email'],
            ];
            if (! empty($data['password'])) {
                $userAttributes['password'] = $data['password'];
            }
            User::query()->where('id', $companyUser->id)->firstOrFail()->update($userAttributes);
        });

        return redirect()->route('companies.index')->with('success', 'Empresa actualizada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCompany(Request $request, ?int $userId = null, bool $passwordRequired = true): array
    {
        $passwordRules = [$passwordRequired ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'];

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:40'],
            'phone_secondary' => ['nullable', 'string', 'max:40'],
            'document' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($userId)],
            'address' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'password' => $passwordRules,
        ]);

        $data['email'] = mb_strtolower(trim($data['email']));
        $data['active'] = $request->boolean('active');

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function companyAttributes(array $data, bool $creating = true): array
    {
        $attributes = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'phone_secondary' => $data['phone_secondary'] ?? null,
            'document' => trim($data['document']),
            'email' => $data['email'],
            'address' => $data['address'] ?? null,
            'active' => $data['active'],
            'updated_at' => now(),
        ];

        if ($creating) {
            $attributes['created_at'] = now();
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function fullName(array $data): string
    {
        return trim($data['first_name'].' '.$data['last_name']);
    }
}
