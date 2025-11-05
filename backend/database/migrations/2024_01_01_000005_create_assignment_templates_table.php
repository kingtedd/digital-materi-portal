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
        Schema::create('assignment_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Template name
            $table->string('title'); // Assignment title
            $table->text('description'); // Body template
            $table->integer('due_offset_days')->default(1); // Offset due date from release
            $table->integer('points')->default(100); // Assignment points
            $table->json('settings')->nullable(); // Additional settings
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_templates');
    }
};