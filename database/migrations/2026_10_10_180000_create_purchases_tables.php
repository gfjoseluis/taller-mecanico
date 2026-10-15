<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $t) {
            $t->id();
            $t->string('folio', 20)->unique();
            $t->foreignId('supplier_id')->constrained('suppliers');
            $t->foreignId('bank_account_id')->nullable()->constrained('bank_accounts');
            $t->string('tipo_pago', 20)->default('contado'); // contado|credito
            $t->decimal('total', 14, 2)->default(0);
            $t->decimal('saldo', 14, 2)->default(0);
            $t->string('estado', 20)->default('vigente')->index();
            $t->text('glosa')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['supplier_id', 'created_at']);
        });
        Schema::create('purchase_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $t->foreignId('product_id')->constrained('products');
            $t->decimal('cantidad', 14, 2);
            $t->decimal('costo', 14, 2);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_lines');
        Schema::dropIfExists('purchases');
    }
};
