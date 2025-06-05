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

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('personal_company');
            $table->string('slug')->unique();
            $table->string('search_slug');
            $table->string('reference')->unique();

            // Faculty & Institute
            $table->string('faculty')->nullable();
            $table->string('institute')->nullable();

            // Group Leader (Principal Investigator)
            $table->string('leader_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('office_address')->nullable();
            $table->string('website')->nullable();
            $table->string('orcid')->nullable();

            // Research Focus
            $table->text('research_keywords')->nullable();
            $table->text('research_description')->nullable();

            // Administrative
            $table->text('funding_sources')->nullable();
            $table->string('preferred_language')->default('english');

            // ELN Information
            $table->boolean('uses_eln')->default(false);
            $table->string('eln_system')->nullable();
            $table->string('eln_other')->nullable();

            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('companies');
        Schema::enableForeignKeyConstraints();
    }
};
