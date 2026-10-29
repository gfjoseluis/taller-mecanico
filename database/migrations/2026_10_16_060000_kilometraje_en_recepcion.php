<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El kilometraje pasa a pertenecer únicamente a la recepción (se lee al ingresar el vehículo).
 * Se preserva lo existente: el km de cada orden se copia a su recepción (se crea si faltaba).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recepciones', function (Blueprint $t) {
            $t->unsignedInteger('kilometraje')->nullable()->after('combustible');
        });

        foreach (DB::table('work_orders')->whereNotNull('kilometraje')->get(['id', 'kilometraje']) as $o) {
            if (DB::table('recepciones')->where('work_order_id', $o->id)->exists()) {
                DB::table('recepciones')->where('work_order_id', $o->id)->update(['kilometraje' => $o->kilometraje]);
            } else {
                // Orden sin recepción: se le crea una mínima con su km para no perder el dato.
                DB::table('recepciones')->insert([
                    'work_order_id' => $o->id,
                    'combustible' => 'medio',
                    'kilometraje' => $o->kilometraje,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('work_orders', function (Blueprint $t) {
            $t->dropColumn('kilometraje');
        });
        Schema::table('proformas', function (Blueprint $t) {
            $t->dropColumn('kilometraje');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $t) {
            $t->unsignedInteger('kilometraje')->nullable()->after('vehicle_id');
        });
        Schema::table('proformas', function (Blueprint $t) {
            $t->unsignedInteger('kilometraje')->nullable();
        });
        DB::statement('UPDATE work_orders o JOIN recepciones r ON r.work_order_id = o.id SET o.kilometraje = r.kilometraje');
        Schema::table('recepciones', function (Blueprint $t) {
            $t->dropColumn('kilometraje');
        });
    }
};
