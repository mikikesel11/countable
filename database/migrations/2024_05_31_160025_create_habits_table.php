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
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['CHECK', 'NUMBER']);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // $table->foreignId('current_streak_id')->constrained()->cascadeOnDelete()->nullable()->default(null);
            // $table->foreignId('longest_streak_id')->constrained()->cascadeOnDelete()->nullable()->default(null);
            $table->boolean('active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habits');
    }
};
