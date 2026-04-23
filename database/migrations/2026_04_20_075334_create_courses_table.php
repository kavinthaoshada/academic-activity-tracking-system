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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches');
            $table->string('name');           // "Java Programming (BCA)"
            $table->string('code')->nullable(); // "OOP-JAVA"
            $table->enum('type', ['theory', 'practical']); // theory or practical
            $table->integer('total_hours');   // 45 or 30 — drives the weekly target calc
            $table->integer('credit_hours')->nullable(); // Cumulative target from report
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
