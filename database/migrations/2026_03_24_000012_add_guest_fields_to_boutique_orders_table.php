<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(env('DB_TABLE_PREFIX', '') . 'boutique_orders', function (Blueprint $table) {
            // Make user_id nullable to support guest orders
            $table->foreignId('user_id')->nullable()->change();
            $table->string('guest_name', 255)->nullable()->after('user_id');
            $table->string('guest_email', 255)->nullable()->after('guest_name');
        });
    }

    public function down(): void
    {
        Schema::table(env('DB_TABLE_PREFIX', '') . 'boutique_orders', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_email']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
