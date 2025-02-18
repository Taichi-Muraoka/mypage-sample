<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_import', function (Blueprint $table) {
            /* カラム */
            $table->date('invoice_date')->comment('請求書年月');
            $table->date('issue_date')->nullable()->comment('発行日');
            $table->date('bill_date')->nullable()->comment('引落日');
            $table->date('start_date')->nullable()->comment('月謝期間開始日');
            $table->date('end_date')->nullable()->comment('月謝期間終了日');
            $table->string('term_text1', 50)->nullable()->comment('月謝期間テキスト1');
            $table->string('term_text2', 50)->nullable()->comment('月謝期間テキスト2');
            $table->unsignedSmallInteger('import_state')->default(0)->comment('取込状態（0:取込未、1:取込済）');
            $table->timestamp('import_date')->nullable()->comment('取込日時');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('invoice_date');

            /* テーブル名コメント */
            $table->comment('請求取込情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_import');
    }
};
