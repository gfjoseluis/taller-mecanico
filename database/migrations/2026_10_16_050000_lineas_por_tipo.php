<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un solo origen para servicio / insumo / repuesto: cada línea lleva su tipo.
 * Las líneas de servicio no tienen producto (por eso product_id pasa a opcional).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['work_order_lines', 'proforma_lines'] as $tabla) {
            Schema::table($tabla, function (Blueprint $t) {
                $t->string('tipo', 20)->default('repuesto')->after('product_id');
                $t->string('descripcion', 255)->nullable()->after('tipo');
            });
        }
        DB::statement('ALTER TABLE work_order_lines MODIFY product_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE proforma_lines MODIFY product_id BIGINT UNSIGNED NULL');
        // Histórico: el tipo sale del producto para no adivinar nunca al imprimir.
        DB::statement('UPDATE work_order_lines l JOIN products p ON p.id = l.product_id SET l.tipo = p.tipo');
        DB::statement('UPDATE proforma_lines l JOIN products p ON p.id = l.product_id SET l.tipo = p.tipo');
    }

    public function down(): void
    {
        foreach (['work_order_lines', 'proforma_lines'] as $tabla) {
            DB::table($tabla)->whereNull('product_id')->delete();
            DB::statement("ALTER TABLE {$tabla} MODIFY product_id BIGINT UNSIGNED NOT NULL");
            Schema::table($tabla, function (Blueprint $t) {
                $t->dropColumn(['tipo', 'descripcion']);
            });
        }
    }
};
