<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Socios SRL (aportes y retiros de capital, no son ventas ni gastos).
        Schema::create('partners', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 150);
            $t->string('ci', 30)->nullable();
            $t->string('telefono', 40)->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        // Punto de partida auditado: desde aquí todo suma/resta con movimientos.
        Schema::table('bank_accounts', function (Blueprint $t) {
            $t->decimal('saldo_inicial', 14, 2)->default(0)->after('saldo');
            $t->timestamp('corte_at')->nullable()->after('saldo_inicial');
        });
        \Illuminate\Support\Facades\DB::statement('UPDATE bank_accounts SET saldo_inicial = saldo, corte_at = NOW() WHERE corte_at IS NULL');

        // Movimientos de dinero (entradas, salidas, traspasos, aportes, retiros).
        Schema::create('bank_movements', function (Blueprint $t) {
            $t->id();
            $t->date('fecha')->index();
            $t->string('tipo', 20)->index(); // entrada|salida|traspaso|aporte|retiro
            $t->foreignId('cuenta_id')->constrained('bank_accounts');
            $t->foreignId('cuenta_destino_id')->nullable()->constrained('bank_accounts');
            $t->foreignId('partner_id')->nullable()->constrained('partners');
            $t->decimal('monto', 14, 2);
            $t->string('motivo', 255);
            $t->string('origen_tipo', 30)->nullable()->index(); // cobro|compra|abono-cliente|abono-proveedor|manual
            $t->unsignedBigInteger('origen_id')->nullable()->index();
            $t->foreignId('user_id')->nullable()->constrained('users');
            $t->boolean('revisado')->default(false)->index();
            $t->timestamps();
            $t->index(['cuenta_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_movements');
        Schema::table('bank_accounts', function (Blueprint $t) {
            $t->dropColumn(['saldo_inicial', 'corte_at']);
        });
        Schema::dropIfExists('partners');
    }
};
