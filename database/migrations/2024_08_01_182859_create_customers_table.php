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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'customers', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('honorific', 15)->nullable();
            $table->string('bp_id', 90)->nullable();
            $table->string('crm_id', 90)->nullable();
            $table->string('incadea_id', 90)->nullable();
            $table->string('rfc', 90)->nullable();
            $table->string('tax_regime', 90)->nullable();
            $table->string('name', 90);
            $table->string('last_name',90);
            $table->integer('age',false,true)->nullable();
            $table->string('birthday')->nullable();
            $table->string('gender',90)->nullable();
            $table->string('phone_1',90)->nullable();
            $table->string('phone_2',90)->nullable();
            $table->string('phone_3',90)->nullable();
            $table->string('cellphone',90)->nullable();
            $table->string('email_1',90)->nullable();
            $table->string('email_2',90)->nullable();
            $table->string('contact_method', 90)->nullable();
            $table->string('zip_code',90)->nullable();
            $table->string('address',90)->nullable();
            $table->string('state',90)->nullable();
            $table->string('city',90)->nullable();
            $table->string('district',90)->nullable();
            $table->string('neighborhood',90)->nullable();
            $table->string('marital_status',90)->nullable();
            $table->string('educational_level',90)->nullable();
            $table->string('origin_agency',90)->nullable();
            $table->string('picture')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'customers');
    }
};
