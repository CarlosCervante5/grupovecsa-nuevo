<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SQLite guardaba type como CHECK solo con 4 valores; la migración MySQL de
 * 'experience' no aplica aquí. Recreamos la tabla sin ese CHECK para alinear
 * con MySQL y permitir type = experience.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $table = env('DB_TABLE_PREFIX', '').'marketing_events';
        $bak = $table.'_bak_typefix';

        Schema::dropIfExists($bak);

        if (! Schema::hasTable($table)) {
            return;
        }

        $row = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
        $ddl = is_object($row) ? (string) ($row->sql ?? '') : '';

        if ($ddl !== '' && ! str_contains($ddl, 'check ("type" in')) {
            $this->ensureUuidUniqueIndex($table);

            return;
        }

        Schema::rename($table, $bak);

        Schema::create($table, function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->string('begin_date');
            $t->string('end_date');
            $t->string('name');
            $t->string('segment_name')->nullable();
            $t->string('type')->default('principal');
            $t->string('page_status')->default('public');
            $t->string('location')->nullable();
            $t->text('description')->nullable();
            $t->string('image_path')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::statement("INSERT INTO `{$table}` SELECT * FROM `{$bak}`");
        Schema::drop($bak);
        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function ensureUuidUniqueIndex(string $table): void
    {
        $idx = $table.'_uuid_unique';
        $exists = DB::selectOne('SELECT 1 FROM sqlite_master WHERE type = ? AND name = ?', ['index', $idx]);
        if ($exists === null) {
            DB::statement("CREATE UNIQUE INDEX \"{$idx}\" ON \"{$table}\" (\"uuid\")");
        }
    }

    public function down(): void
    {
        // No restauramos el CHECK antiguo para no romper filas con type=experience.
    }
};
