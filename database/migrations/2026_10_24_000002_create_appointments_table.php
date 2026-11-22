<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One appointment = one dose for one child in one time slot. A visit for several
     * vaccines is several appointments sharing a slot. The vaccine and dose number
     * come from child_dose → vaccine_schedule.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->restrictOnDelete();
            $table->foreignId('child_dose_id')->constrained('child_doses')->restrictOnDelete();
            $table->foreignId('hospital_id')->constrained()->restrictOnDelete();
            $table->foreignId('time_slot_id')->constrained()->restrictOnDelete();
            // The batch holding this appointment's reserved dose.
            $table->foreignId('hospital_stock_id')->constrained('hospital_stock')->restrictOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed', 'no_show'])
                ->default('pending')->index();
            $table->string('reason', 500)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('no_show_at')->nullable();
            $table->timestamps();

            // Database-level backstop for "one open booking per dose": holds the dose id
            // only while the appointment is pending or approved, so a second open
            // booking for the same dose violates the unique index.
            $table->unsignedBigInteger('open_child_dose_id')
                ->storedAs("IF(`status` IN ('pending', 'approved'), `child_dose_id`, NULL)")
                ->nullable()
                ->unique();

            $table->index(['time_slot_id', 'status']);
            $table->index(['hospital_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
