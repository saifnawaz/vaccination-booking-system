<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per scheduled dose per child, generated when the child is added.
     * due_date is the age-based date (date of birth + age_days). The effective
     * due date and the status (completed/due/upcoming/overdue/missed) are worked
     * out at read time by App\Services\VaccinationSchedule, so they never go stale.
     */
    public function up(): void
    {
        Schema::create('child_doses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->restrictOnDelete();
            $table->foreignId('vaccine_schedule_id')->constrained('vaccine_schedule')->restrictOnDelete();
            $table->date('due_date');
            $table->date('administered_at')->nullable();
            // The batch the dose came from, once given.
            $table->foreignId('hospital_stock_id')->nullable()->constrained('hospital_stock')->restrictOnDelete();
            $table->timestamps();

            // A child has each scheduled dose exactly once.
            $table->unique(['child_id', 'vaccine_schedule_id']);
            $table->index(['administered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_doses');
    }
};
