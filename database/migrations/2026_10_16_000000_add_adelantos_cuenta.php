<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Dinero recibido antes de cerrar la orden (pasivo: se debe el servicio o la devolución).
        \App\Models\ChartAccount::firstOrCreate(['codigo' => '2.2'], [
            'nombre' => 'Adelantos de clientes', 'tipo' => 'pasivo', 'nivel' => 2, 'imputable' => true,
        ]);
    }

    public function down(): void {}
};
