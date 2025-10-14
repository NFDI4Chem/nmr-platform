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
        Schema::create('impurities', function (Blueprint $table) {
            $table->id();
            $table->json('names'); // Array of compound names
            $table->string('smiles')->nullable(); // SMILES notation
            $table->json('ranges'); // NMR ranges data
            $table->string('nucleus', 10); // Nucleus type (1H, 13C)
            $table->string('solvent', 50); // Solvent name
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Add indexes for better query performance
            $table->index('nucleus');
            $table->index('solvent');
            $table->index(['nucleus', 'solvent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impurities');
    }
};
