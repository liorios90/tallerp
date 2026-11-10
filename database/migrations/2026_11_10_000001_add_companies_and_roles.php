<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 40);
            $table->string('phone_secondary', 40)->nullable();
            $table->string('document', 40);
            $table->string('email');
            $table->string('address')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        foreach (['customers', 'service_orders', 'inventory_items', 'purchases', 'invoices', 'inventory_movements'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            });
        }

        $hasWorkshopData = DB::table('users')->exists()
            || DB::table('customers')->exists()
            || DB::table('service_orders')->exists()
            || DB::table('inventory_items')->exists()
            || DB::table('purchases')->exists()
            || DB::table('invoices')->exists();

        if ($hasWorkshopData) {
            $user = DB::table('users')->orderBy('id')->first();
            $name = trim((string) ($user->name ?? 'Taller Principal'));
            $parts = preg_split('/\s+/', $name, 2) ?: [];
            $companyId = DB::table('companies')->insertGetId([
                'first_name' => $parts[0] ?: 'Taller',
                'last_name' => $parts[1] ?? 'Principal',
                'phone' => '0000000000',
                'document' => '0000000000',
                'email' => $user->email ?? 'taller@local.test',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->update([
                'company_id' => $companyId,
                'role' => 'usuario',
            ]);

            foreach (['customers', 'service_orders', 'inventory_items', 'purchases', 'invoices', 'inventory_movements'] as $tableName) {
                DB::table($tableName)->update(['company_id' => $companyId]);
            }
        }

        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropUnique(['sku']);
            $table->unique(['company_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table): void {
            $table->dropUnique(['company_id', 'sku']);
            $table->unique('sku');
        });

        foreach (['inventory_movements', 'invoices', 'purchases', 'inventory_items', 'service_orders', 'customers'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('company_id');
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::dropIfExists('companies');
    }
};
