<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('type', 20)->default('normal')->after('active');
            $table->unsignedSmallInteger('demo_days')->nullable()->after('type');
            $table->date('registered_at')->nullable()->after('demo_days');
            $table->date('starts_at')->nullable()->after('registered_at');
        });

        DB::table('companies')->whereNull('registered_at')->update([
            'registered_at' => DB::raw('DATE(created_at)'),
            'starts_at' => DB::raw('DATE(created_at)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn(['type', 'demo_days', 'registered_at', 'starts_at']);
        });
    }
};
