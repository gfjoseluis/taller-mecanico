<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key', 120)->unique();
            $t->text('value')->nullable();
            $t->string('grupo', 60)->default('general');
            $t->timestamps();
        });

        Schema::create('sku_counters', function (Blueprint $t) {
            $t->string('tipo', 30)->primary();
            $t->unsignedBigInteger('ultimo')->default(0);
            $t->timestamps();
        });

        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 120);
            $t->string('prefijo', 8);
            $t->string('tipo', 30)->default('repuesto');
            $t->timestamps();
            $t->softDeletes();
            $t->index('nombre');
        });

        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 150);
            $t->string('nit', 30)->nullable()->index();
            $t->string('telefono', 40)->nullable();
            $t->string('direccion', 200)->nullable();
            $t->decimal('saldo', 14, 2)->default(0);
            $t->timestamps();
            $t->softDeletes();
            $t->index('nombre');
        });

        Schema::create('bank_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 120);
            $t->string('banco', 120);
            $t->string('numero', 60)->unique();
            $t->string('moneda', 5)->default('BOB');
            $t->decimal('saldo', 14, 2)->default(0);
            $t->boolean('activa')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 150);
            $t->string('nit_ci', 30)->index();
            $t->string('telefono', 40)->nullable();
            $t->string('email', 120)->nullable();
            $t->string('direccion', 220)->nullable();
            $t->decimal('saldo', 14, 2)->default(0);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['nombre', 'nit_ci']);
        });

        Schema::create('vehicles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained('clients');
            $t->string('placa', 20)->unique();
            $t->string('marca', 80)->nullable();
            $t->string('modelo', 80)->nullable();
            $t->string('anio', 10)->nullable();
            $t->string('chasis', 60)->nullable()->index();
            $t->decimal('tonelaje', 8, 2)->nullable();
            $t->unsignedTinyInteger('ejes')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['client_id', 'placa']);
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('sku', 40)->unique();
            $t->string('nombre', 160);
            $t->string('tipo', 20)->default('repuesto')->index();
            $t->foreignId('category_id')->nullable()->constrained('categories');
            $t->string('unidad', 20)->default('pza');
            $t->decimal('precio_compra', 14, 2)->default(0);
            $t->decimal('precio_venta', 14, 2)->default(0);
            $t->decimal('stock', 14, 2)->default(0);
            $t->decimal('stock_minimo', 14, 2)->default(0);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['nombre', 'tipo']);
            $t->index('stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('sku_counters');
        Schema::dropIfExists('settings');
    }
};
