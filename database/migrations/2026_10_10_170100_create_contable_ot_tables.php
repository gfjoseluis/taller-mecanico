<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('codigo', 20)->unique();
            $t->string('nombre', 150);
            $t->string('tipo', 30)->index(); // activo,pasivo,patrimonio,ingreso,gasto
            $t->unsignedTinyInteger('nivel')->default(1);
            $t->foreignId('padre_id')->nullable()->constrained('chart_accounts');
            $t->boolean('imputable')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('journal_entries', function (Blueprint $t) {
            $t->id();
            $t->date('fecha')->index();
            $t->string('glosa', 255);
            $t->string('modulo', 40)->index(); // ventas,compras,tesoreria,ot,manual
            $t->string('referencia', 60)->nullable()->index();
            $t->foreignId('user_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('journal_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('journal_id')->constrained('journal_entries')->cascadeOnDelete();
            $t->foreignId('account_id')->constrained('chart_accounts');
            $t->decimal('debe', 14, 2)->default(0);
            $t->decimal('haber', 14, 2)->default(0);
            $t->timestamps();
            $t->index(['account_id', 'journal_id']);
        });

        Schema::create('work_orders', function (Blueprint $t) {
            $t->id();
            $t->string('numero', 20)->unique();
            $t->foreignId('client_id')->constrained('clients');
            $t->foreignId('vehicle_id')->constrained('vehicles');
            $t->string('estado', 30)->default('registrada')->index();
            $t->text('diagnostico')->nullable();
            $t->decimal('mano_obra', 14, 2)->default(0);
            $t->decimal('total_repuestos', 14, 2)->default(0);
            $t->decimal('total', 14, 2)->default(0);
            $t->foreignId('tecnico_id')->nullable()->constrained('users');
            $t->timestamp('cerrada_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['estado', 'created_at']);
        });

        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('cuf', 120)->unique()->nullable();
            $t->unsignedBigInteger('numero')->nullable()->index();
            $t->string('origen_tipo', 30)->index(); // ot,venta
            $t->unsignedBigInteger('origen_id')->index();
            $t->foreignId('client_id')->constrained('clients');
            $t->decimal('total', 14, 2);
            $t->string('estado', 30)->default('pendiente')->index(); // pendiente,validada,anulada,error
            $t->text('qr')->nullable();
            $t->json('raw')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_accounts');
    }
};
