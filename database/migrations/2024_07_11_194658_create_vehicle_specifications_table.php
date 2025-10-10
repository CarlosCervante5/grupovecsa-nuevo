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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'vehicle_specifications', function (Blueprint $table) {
            $table->id();
            $table->integer('keys_number',false,true)->default(1);
            $table->enum('wheel_locks',['yes','no'])->nullable();
            $table->enum('spare_wheel',['yes','no'])->nullable();
            $table->enum('hydraulic_jack',['yes','no'])->nullable();
            $table->enum('fire_extinguisher',['yes','no'])->nullable();
            $table->enum('reflectors',['yes','no'])->nullable();
            $table->enum('jumper_cables',['yes','no'])->nullable();
            $table->string('engine_type')->nullable();
            $table->string('plates')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->enum('auto_start_stop',['yes','no'])->nullable();
            $table->enum('tools',['yes','no'])->nullable();
            $table->enum('antenna',['yes','no'])->nullable();
            $table->enum('stud_wrench',['yes','no'])->nullable();
            $table->enum('security_film',['yes','no'])->nullable();
            $table->enum('warranty_policy', ['yes','no'])->nullable();
            $table->enum('warranty_manual', ['yes','no'])->nullable();
            $table->string('intake_engine')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'vehicles')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'vehicle_specifications');
    }
};
