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
        Schema::disableForeignKeyConstraints();

        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('identifier')->nullable();
            $table->foreignId('solvent_id')->nullable()->constrained();
            $table->foreignId('molecule_id')->nullable()->constrained();
            $table->string('other_nuclei')->nullable();
            $table->boolean('automation')->default(false);
            $table->string('featured_molfile_id')->nullable();
            $table->text('instructions')->nullable();
            $table->string('featured_image_id')->nullable();
            $table->enum('priority', ['HIGH', 'MEDIUM', 'LOW'])->default('LOW');
            $table->foreignId('operator_id')->nullable();
            $table->string('status')->default('Draft');
            $table->string('finished_file_id');
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
