<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = (string) config('vecsa.db_table_prefix', '');
        $conversations = $prefix.'assistant_conversations';
        $messages = $prefix.'assistant_messages';

        if (! Schema::hasTable($conversations)) {
        Schema::create($conversations, function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('session_key', 64)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('visitor_name')->nullable();
            $table->string('visitor_email')->nullable();
            $table->string('page_url', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('preview', 500)->nullable();
            $table->unsignedInteger('messages_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
        }

        if (! Schema::hasTable($messages)) {
        Schema::create($messages, function (Blueprint $table) use ($conversations) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->string('role', 20);
            $table->text('content');
            $table->timestamps();

            $table->foreign('conversation_id')
                ->references('id')
                ->on($conversations)
                ->cascadeOnDelete();
        });
        }
    }

    public function down(): void
    {
        $prefix = (string) config('vecsa.db_table_prefix', '');
        Schema::dropIfExists($prefix.'assistant_messages');
        Schema::dropIfExists($prefix.'assistant_conversations');
    }
};
