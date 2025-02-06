<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('datahub_clinic_id')->nullable();
            $table->string('title')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('clinic_name')->nullable();
            $table->string('clinic_handle')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('doctor_handle')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('street')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_doctor')->default(false);
            $table->boolean('only_private_patients')->default(false);
            $table->boolean('is_allergy_specialist')->default(true);
            $table->boolean('is_allergy_diagnostic')->default(false);
            $table->boolean('is_subcutaneous_immunotherapy')->default(false);
            $table->boolean('is_sublingual_immunotherapy')->default(false);
            $table->boolean('is_venom_immunotherapy')->default(false);
            $table->string('online_appointment_url', 1000)->nullable();
            $table->string('telehealth', 1000)->nullable();
            $table->string('waiting_time')->nullable();
            $table->string('description')->nullable();
            $table->longText('other')->nullable();
            $table->boolean('manual_inserted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinics');
    }
};
