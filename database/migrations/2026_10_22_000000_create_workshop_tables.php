<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) { $table->string('role')->default('Recepcionista'); });
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('document')->nullable()->index(); $table->string('address')->nullable();
            $table->string('phone')->nullable()->index(); $table->string('email')->nullable()->index(); $table->timestamps();
        });
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id(); $table->string('number')->unique(); $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('contact_name')->nullable(); $table->string('contact_phone')->nullable();
            $table->string('device_type'); $table->string('brand')->nullable(); $table->string('model')->nullable(); $table->string('color')->nullable();
            $table->string('imei1')->nullable(); $table->string('imei2')->nullable(); $table->string('serial')->nullable(); $table->text('unlock_method')->nullable();
            $table->string('battery_state')->nullable(); $table->boolean('powered_off')->default(false); $table->text('observations')->nullable();
            $table->text('requested_work')->nullable(); $table->text('diagnosis')->nullable(); $table->string('status')->default('Recibido');
            $table->decimal('estimated_cost', 10, 2)->default(0); $table->timestamp('received_at'); $table->timestamps();
        });
        Schema::create('order_photos', function (Blueprint $table) { $table->id(); $table->foreignId('service_order_id')->constrained()->cascadeOnDelete(); $table->string('path'); $table->string('caption')->nullable(); $table->timestamps(); });
        Schema::create('order_accessories', function (Blueprint $table) { $table->id(); $table->foreignId('service_order_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->string('condition')->default('Bueno'); $table->timestamps(); });
        Schema::create('checklist_checks', function (Blueprint $table) { $table->id(); $table->foreignId('service_order_id')->constrained()->cascadeOnDelete(); $table->string('item'); $table->string('result'); $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('checked_at'); });
        Schema::create('inventory_items', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('sku')->nullable()->unique(); $table->string('category')->nullable(); $table->integer('quantity')->default(0); $table->integer('min_quantity')->default(3); $table->decimal('unit_cost',10,2)->default(0); $table->timestamps(); });
        Schema::create('invoices', function (Blueprint $table) { $table->id(); $table->foreignId('service_order_id')->constrained()->cascadeOnDelete(); $table->string('number')->unique(); $table->decimal('subtotal',10,2)->default(0); $table->decimal('tax',10,2)->default(0); $table->decimal('total',10,2)->default(0); $table->string('status')->default('Pendiente'); $table->timestamp('issued_at')->nullable(); $table->timestamps(); });
    }
    public function down(): void { foreach (['invoices','inventory_items','checklist_checks','order_accessories','order_photos','service_orders','customers'] as $table) Schema::dropIfExists($table); Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role')); }
};
