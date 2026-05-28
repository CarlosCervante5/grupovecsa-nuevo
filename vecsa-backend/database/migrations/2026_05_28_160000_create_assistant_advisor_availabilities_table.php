<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = (string) config('vecsa.db_table_prefix', '');
        $table = $prefix.'assistant_advisor_availabilities';
        $users = 'users';
        $dealerships = (new \App\Models\Dealership)->getTable();

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $blueprint) use ($users, $dealerships) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('user_id');
            $blueprint->unsignedBigInteger('dealership_id');
            $blueprint->boolean('is_available')->default(false);
            $blueprint->timestamp('available_since')->nullable();
            $blueprint->timestamps();

            $blueprint->unique(['user_id', 'dealership_id']);
            $blueprint->index(['dealership_id', 'is_available']);

            $blueprint->foreign('user_id')->references('id')->on($users)->cascadeOnDelete();
            $blueprint->foreign('dealership_id')->references('id')->on($dealerships)->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $prefix = (string) config('vecsa.db_table_prefix', '');
        Schema::dropIfExists($prefix.'assistant_advisor_availabilities');
    }
};
