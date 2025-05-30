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
        Schema::table('facturas_electronicas', function (Blueprint $table) {
            $table->boolean('qr_generado')->default(false)->comment('Indica si el QR fue generado automáticamente')->after('observacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facturas_electronicas', function (Blueprint $table) {
            $table->dropColumn('qr_generado');
        });
    }
};
