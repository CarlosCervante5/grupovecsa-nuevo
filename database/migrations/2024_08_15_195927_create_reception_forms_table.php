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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'reception_forms', function (Blueprint $table) {
            $table->id();
            $table->string('date')->nullable();
            $table->string('hour')->nullable();
            $table->string('salesAdvisor')->nullable();
            $table->string('brand')->nullable();
            $table->string('departureTime')->nullable();
            $table->string('visitType')->nullable();
            $table->string('visitFirsTime')->nullable();
            $table->string('department')->nullable();
            $table->string('howFindOut')->nullable();
            $table->string('contactSub')->nullable();
            $table->string('clientName')->nullable();
            $table->string('clientAge')->nullable();
            $table->string('clientPhone')->nullable();
            $table->string('clientEmail')->nullable();
            $table->string('preferredMedium')->nullable();
            $table->string('model')->nullable();
            $table->integer('year')->nullable();
            $table->string('version')->nullable();
            $table->string('color')->nullable();
            $table->string('accessories')->nullable();
            $table->string('brandSecondOption')->nullable();
            $table->string('modelSecondOption')->nullable();
            $table->string('versionSecondOption')->nullable();
            $table->string('colorSecondOption')->nullable();
            $table->string('testDrive')->nullable();
            $table->string('receivedQuote')->nullable();
            $table->string('FAndI')->nullable();
            $table->string('leaveCarOnAccount')->nullable();
            $table->string('customersCurrentCar')->nullable();
            $table->string('hostes')->nullable();
            $table->string('wasClientProfile')->nullable();
            $table->string('wasApplicationTaken')->nullable();
            $table->text('commentaryFandI')->nullable();
            $table->string('financingType')->nullable();
            $table->string('initialInvestment')->nullable();
            $table->string('monthlyPayment')->nullable();
            $table->string('termHowManyMonths')->nullable();
            $table->text('commentaryFinancing')->nullable();
            $table->string('segment')->nullable();
            $table->string('idCRM')->nullable();
            $table->string('honorific')->nullable();
            $table->string('contactSalesplace')->nullable();
            $table->string('saleType')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'reception_forms');
    }
};
