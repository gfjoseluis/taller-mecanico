<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hoja de recepción: cómo llega el vehículo (fotos en fase 2).
        Schema::create('recepciones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_order_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('combustible', 20)->default('medio'); // vacio|cuarto|medio|tres_cuartos|lleno
            $t->json('inventario')->nullable(); // claves marcadas OK
            $t->string('inventario_otros', 255)->nullable();
            $t->json('inspeccion')->nullable(); // claves OK
            $t->text('observaciones')->nullable();
            $t->foreignId('user_id')->nullable()->constrained('users');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepciones');
    }
};
