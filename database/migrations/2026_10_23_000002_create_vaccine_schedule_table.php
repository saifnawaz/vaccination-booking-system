<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccine_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vaccine_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('dose_number');
            $table->string('label', 100);
            $table->unsignedSmallInteger('age_days');
            $table->unsignedSmallInteger('min_interval_days')->default(28);
            $table->unsignedSmallInteger('max_age_days')->nullable();
            $table->unsignedSmallInteger('grace_days')->default(28);
            $table->timestamps();

            $table->unique(['vaccine_id', 'dose_number']);
            $table->index('age_days');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccine_schedule');
    }
};
