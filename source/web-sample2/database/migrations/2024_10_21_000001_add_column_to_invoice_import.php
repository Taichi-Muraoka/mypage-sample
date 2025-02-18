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
        Schema::table('invoice_import', function (Blueprint $table) {
            // 列追加
            $table->unsignedSmallInteger('mail_state')
                ->default(0)
                ->comment('メール送信状態（0:送信未、1:送信済）')
                ->after('import_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_import', function (Blueprint $table) {
            // 列削除
            $table->dropColumn('mail_state');
        });
    }
};
