<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $t) {
            $t->foreignId('parent_id')->nullable()->after('vehicle_id')->constrained('work_orders');
        });
        Schema::create('work_order_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users');
            $t->string('accion', 40); // creada, editada, item_agregado, cerrada, anulada, complementaria
            $t->json('detalle')->nullable();
            $t->timestamps();
            $t->index(['work_order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_logs');
        Schema::table('work_orders', function (Blueprint $t) {
            $t->dropConstrainedForeignId('parent_id');
        });
    }
};
