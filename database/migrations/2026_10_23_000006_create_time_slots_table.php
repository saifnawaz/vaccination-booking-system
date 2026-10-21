<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('capacity');
            $table->timestamps();

            $table->unique(['hospital_id', 'date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
