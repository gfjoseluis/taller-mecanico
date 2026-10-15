<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Abonos a importadoras (dinero que sale), espejo de cobros (dinero que entra).
        Schema::create('supplier_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('purchase_id')->constrained('purchases');
            $t->string('medio', 20); // efectivo|transferencia
            $t->foreignId('bank_account_id')->nullable()->constrained('bank_accounts');
            $t->decimal('monto', 14, 2);
            $t->foreignId('user_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->index('purchase_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
