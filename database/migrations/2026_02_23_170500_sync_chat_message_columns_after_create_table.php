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
        if (! Schema::hasTable('chat_messages')) {
            return;
        }

        if (! Schema::hasColumn('chat_messages', 'message_type')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->string('message_type', 20)->default('text');
            });
        }

        if (! Schema::hasColumn('chat_messages', 'attachment_path')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->string('attachment_path')->nullable();
            });
        }

        if (! Schema::hasColumn('chat_messages', 'attachment_name')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->string('attachment_name')->nullable();
            });
        }

        if (! Schema::hasColumn('chat_messages', 'attachment_mime')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->string('attachment_mime')->nullable();
            });
        }

        if (! Schema::hasColumn('chat_messages', 'attachment_size')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->unsignedBigInteger('attachment_size')->nullable();
            });
        }

        if (! Schema::hasColumn('chat_messages', 'view_once')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->boolean('view_once')->default(false);
            });
        }

        if (! Schema::hasColumn('chat_messages', 'opened_at')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->timestamp('opened_at')->nullable();
            });
        }

        if (! Schema::hasColumn('chat_messages', 'metadata')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->json('metadata')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep columns in place on rollback to avoid dropping existing chat data.
    }
};
