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
        Schema::create('facturas_electronicas', function (Blueprint $table) {
            $table->id();
            $table->string('cdc', 44)->unique()->comment('Código de Control');
            $table->unsignedTinyInteger('tipo_documento')->comment('Tipo de Documento Electrónico');
            $table->string('establecimiento', 3)->comment('Código de Establecimiento');
            $table->string('punto', 3)->comment('Punto de Expedición');
            $table->string('numero', 7)->comment('Número de Documento');
            $table->dateTime('fecha')->comment('Fecha y Hora de Emisión');
            $table->string('ruc_emisor')->comment('RUC del Emisor');
            $table->string('razon_social_emisor')->comment('Razón Social del Emisor');
            $table->string('ruc_receptor')->comment('RUC del Receptor');
            $table->string('razon_social_receptor')->comment('Razón Social del Receptor');
            $table->decimal('total', 15, 2)->comment('Monto Total');
            $table->decimal('impuesto', 15, 2)->default(0)->comment('Monto Total de Impuestos');
            $table->string('moneda', 3)->default('PYG')->comment('Moneda');
            $table->text('xml')->nullable()->comment('XML del documento electrónico');
            $table->string('estado')->default('generada')->comment('Estado del documento');
            $table->text('observacion')->nullable()->comment('Observaciones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas_electronicas');
    }
};
