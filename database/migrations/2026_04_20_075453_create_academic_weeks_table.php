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
        Schema::create('academic_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches');
            $table->integer('week_number');       // 1 through 15 (or total_weeks)
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('working_days');      // 4, 5, or 6
            $table->enum('week_type', ['full', 'reduced', 'holiday', 'custom']);
            // full = 6 days, reduced = 5 days, holiday = <5 days, custom = admin-defined
            $table->text('notes')->nullable();    // e.g., "Diwali holiday — 3 working days"
            $table->boolean('is_locked')->default(false); // Prevents editing after sessions logged
            $table->timestamps();

            $table->unique(['batch_id', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_weeks');
    }
};
