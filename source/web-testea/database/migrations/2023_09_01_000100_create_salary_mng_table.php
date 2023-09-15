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
        Schema::create('salary_mng', function (Blueprint $table) {
            /* カラム */
            $table->date('salary_date')->comment('給与年月');
            $table->date('confirm_date')->nullable()->comment('確定日');
            $table->unsignedSmallInteger('state')->default(0)->comment('処理状態（0:未処理、1:集計済、2:確定済）');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('salary_date');

            /* テーブル名コメント */
            $table->comment('給与算出管理情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_mng');
    }
};
