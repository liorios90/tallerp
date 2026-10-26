<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->string('status')->default('Borrador')->after('total');
            $table->timestamp('finalized_at')->nullable()->after('status');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('status')->default('Borrador')->change();
            $table->timestamp('finalized_at')->nullable()->after('issued_at');
        });

        DB::table('invoices')->whereIn('status', ['Pendiente', 'Emitida'])->update([
            'status' => 'Finalizada',
            'finalized_at' => DB::raw('COALESCE(issued_at, updated_at, created_at)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn('finalized_at');
            $table->string('status')->default('Pendiente')->change();
        });

        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropColumn(['status', 'finalized_at']);
        });
    }
};
