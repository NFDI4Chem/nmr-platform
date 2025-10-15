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
            $table->string('reference')->nullable();
            $table->string('ticker_id')->nullable();
            $table->string('personal_key')->nullable();
            $table->foreignId('solvent_id')->nullable()->constrained();
            $table->foreignId('molecule_id')->nullable()->constrained();
            $table->string('other_nuclei')->nullable();
            $table->boolean('automation')->default(false);
            $table->string('molfile_id')->nullable();
            $table->text('instructions')->nullable();
            $table->string('additional_infofile_id')->nullable();
            $table->enum('priority', ['HIGH', 'MEDIUM', 'LOW'])->default('LOW');
            $table->foreignId('operator_id')->nullable();
            $table->string('status')->default('Draft');
            $table->string('rawdata_file_id')->nullable();
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
