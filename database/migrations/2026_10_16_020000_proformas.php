<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pro-forma: presupuesto sin stock ni dinero; al aceptar nace la OT.
        Schema::create('proformas', function (Blueprint $t) {
            $t->id();
            $t->string('numero', 20)->unique();
            $t->foreignId('client_id')->constrained();
            $t->foreignId('vehicle_id')->constrained();
            $t->unsignedInteger('kilometraje')->nullable();
            $t->text('diagnostico')->nullable();
            $t->decimal('mano_obra', 12, 2)->default(0);
            $t->decimal('total_repuestos', 12, 2)->default(0);
            $t->decimal('descuento', 12, 2)->default(0);
            $t->decimal('total', 12, 2)->default(0);
            $t->string('estado', 20)->default('pendiente'); // pendiente|aceptada|rechazada
            $t->foreignId('user_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('proforma_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('proforma_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained();
            $t->decimal('cantidad', 12, 2);
            $t->decimal('precio', 12, 2);
        });
        Schema::table('work_orders', function (Blueprint $t) {
            $t->foreignId('proforma_id')->nullable()->after('parent_id')->constrained('proformas');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $t) {
            $t->dropConstrainedForeignId('proforma_id');
        });
        Schema::dropIfExists('proforma_lines');
        Schema::dropIfExists('proformas');
    }
};
