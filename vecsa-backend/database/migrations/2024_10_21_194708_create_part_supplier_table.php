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
        $tableName = env('DB_TABLE_PREFIX', '') . 'part_supplier';

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('delivery_date');
            $table->string('quote_type');
            $table->double('cost',10,2);
            $table->foreignId('supplier_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'spare_suppliers')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('part_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'valuation_parts')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'part_supplier');
    }
};
