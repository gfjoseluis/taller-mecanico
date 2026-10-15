<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cobros: cómo pagó el cliente al cerrar (efectivo, transferencia o crédito).
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_order_id')->constrained('work_orders');
            $t->string('medio', 20); // efectivo|transferencia|crédito
            $t->foreignId('bank_account_id')->nullable()->constrained('bank_accounts');
            $t->decimal('total', 14, 2);
            $t->decimal('recibido', 14, 2)->default(0);
            $t->decimal('vuelto', 14, 2)->default(0);
            $t->decimal('saldo_pendiente', 14, 2)->default(0);
            $t->foreignId('user_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->index('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
