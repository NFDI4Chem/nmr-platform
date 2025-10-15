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
        Schema::create('solvents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('molecular_formula', 100)->nullable()->index();
            $table->decimal('molecular_weight', 10, 2)->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();

            // Add unique constraint on name to prevent duplicate solvents
            $table->unique('name');

            // Add index for better performance on molecular formula searches
            $table->index(['name', 'molecular_formula']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solvents');
    }
};
