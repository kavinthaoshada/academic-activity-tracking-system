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
        Schema::create('weekly_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('academic_week_id')->constrained('academic_weeks');
            $table->foreignId('user_id')->constrained('users'); // who logged this entry

            // Weekly report columns
            $table->integer('planned_sessions')->default(0);
            $table->integer('actual_sessions')->default(0);
            $table->integer('weekly_variance')->storedAs('actual_sessions - planned_sessions');
            // ^ Stored generated column — DB computes it automatically

            // Cumulative report columns (calculated by SessionService, stored for report speed)
            $table->integer('cumulative_target')->default(0);
            $table->integer('cumulative_planned')->default(0);
            $table->integer('cumulative_actual')->default(0);
            $table->integer('cumulative_variance')->storedAs('cumulative_actual - cumulative_planned');

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'academic_week_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_sessions');
    }
};
