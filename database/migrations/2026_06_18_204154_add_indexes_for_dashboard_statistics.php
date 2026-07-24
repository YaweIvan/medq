<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'is_approved']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->change();
            $table->integer('sort_order')->default(0)->after('marks_per_question');
            $table->index(['sort_order', 'created_at']);
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_approved']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['sort_order', 'created_at']);
            $table->dropColumn('sort_order');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};