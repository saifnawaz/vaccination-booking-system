<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per batch. Available = quantity - reserved.
     * quantity: doses physically on hand; reserved: held by open bookings (Phase 3).
     */
    public function up(): void
    {
        Schema::create('hospital_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->restrictOnDelete();
            $table->foreignId('vaccine_id')->constrained()->restrictOnDelete();
            $table->string('batch_number', 50);
            $table->date('expiry_date');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('reserved')->default(0);
            $table->timestamps();

            $table->unique(['hospital_id', 'vaccine_id', 'batch_number']);
            $table->index(['hospital_id', 'vaccine_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_stock');
    }
};
