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
        Schema::table('surcharges', function (Blueprint $table) {
            // 列追加
            $table->unsignedInteger('approval_user')
                ->nullable()
                ->comment('承認者ID')
                ->after('admin_comment');
            $table->timestamp('approval_time')
                ->nullable()
                ->comment('承認日時')
                ->after('approval_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surcharges', function (Blueprint $table) {
            // 列削除
            $table->dropColumn('approval_user');
            $table->dropColumn('approval_time');
        });
    }
};
