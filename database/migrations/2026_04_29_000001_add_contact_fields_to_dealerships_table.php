<?php

/**
 * Columnas de contacto: en instalaciones nuevas ya vienen en create_dealerships_table (2024_07_11_153340).
 * Esta migración solo aplica en bases donde la tabla existía sin phone/email/whatsapp_phone.
 */
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
            if (! Schema::hasColumn($tableName, 'phone')) {
                $table->string('phone', 50)->nullable()->after('description');
            }
            if (! Schema::hasColumn($tableName, 'email')) {
                $table->string('email', 255)->nullable()->after('phone');
            }
            if (! Schema::hasColumn($tableName, 'whatsapp_phone')) {
                $table->string('whatsapp_phone', 50)->nullable()->after('email');
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
            foreach (['whatsapp_phone', 'email', 'phone'] as $col) {
                if (Schema::hasColumn($tableName, $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
