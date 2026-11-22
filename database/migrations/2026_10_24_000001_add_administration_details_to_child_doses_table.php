<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How a dose came to be recorded:
     *   given_here         given at a hospital through an appointment in this app;
     *                      hospital_stock_id is the batch, batch_number copies its number.
     *   historical_record  copied by a hospital from the child's previous records (e.g. a
     *                      vaccination card); no batch in stock, batch_number is free text,
     *                      administered_by_hospital_id is the hospital that entered the record.
     */
    public function up(): void
    {
        Schema::table('child_doses', function (Blueprint $table) {
            $table->foreignId('administered_by_hospital_id')->nullable()->after('administered_at')
                ->constrained('hospitals')->restrictOnDelete();
            $table->string('batch_number', 50)->nullable()->after('hospital_stock_id');
            $table->enum('source', ['given_here', 'historical_record'])->nullable()->after('batch_number');
        });
    }

    public function down(): void
    {
        Schema::table('child_doses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('administered_by_hospital_id');
            $table->dropColumn(['batch_number', 'source']);
        });
    }
};
