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
        Schema::table('schedules', function (Blueprint $table) {
            // インデックス追加
            $table->index('target_date', 'schedule_INDEX1');
            $table->index('campus_cd', 'schedule_INDEX2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // インデックス削除
            $table->dropIndex('schedule_INDEX1');
            $table->dropIndex('schedule_INDEX2');
        });
    }
};
