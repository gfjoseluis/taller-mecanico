<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $t) {
            $t->decimal('descuento', 12, 2)->default(0)->after('total_repuestos');
            $t->unsignedInteger('kilometraje')->nullable()->after('vehicle_id');
        });
        // Descuentos otorgados (menor ingreso, nace deudora).
        \App\Models\ChartAccount::firstOrCreate(['codigo' => '4.3'], [
            'nombre' => 'Descuentos en ventas', 'tipo' => 'ingreso', 'nivel' => 2, 'imputable' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $t) {
            $t->dropColumn(['descuento', 'kilometraje']);
        });
    }
};
