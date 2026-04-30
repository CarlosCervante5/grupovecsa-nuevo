<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = env('DB_TABLE_PREFIX', '') . 'dealerships';

        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'state')) {
                $table->string('state', 120)->nullable()->after('location');
            }
            if (! Schema::hasColumn($tableName, 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('whatsapp_phone');
            }
            if (! Schema::hasColumn($tableName, 'longitude')) {
                $table->decimal('longitude', 11, 7)->nullable()->after('latitude');
            }
            if (! Schema::hasColumn($tableName, 'image_url')) {
                $table->string('image_url', 2048)->nullable()->after('longitude');
            }
            if (! Schema::hasColumn($tableName, 'opening_hours')) {
                $table->string('opening_hours', 255)->nullable()->after('image_url');
            }
        });
    }

    public function down(): void
    {
        $tableName = env('DB_TABLE_PREFIX', '') . 'dealerships';

        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            foreach (['opening_hours', 'image_url', 'longitude', 'latitude', 'state'] as $col) {
                if (Schema::hasColumn($tableName, $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
