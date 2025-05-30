<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('factura_electronica_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_electronica_id')
                ->constrained('facturas_electronicas')
                ->onDelete('cascade');
            $table->string('tipo')->comment('Tipo de evento');
            $table->string('descripcion')->comment('Descripción del evento');
            $table->json('datos')->nullable()->comment('Datos adicionales del evento');
            $table->text('xml')->nullable()->comment('XML del evento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_electronica_eventos');
    }
};
