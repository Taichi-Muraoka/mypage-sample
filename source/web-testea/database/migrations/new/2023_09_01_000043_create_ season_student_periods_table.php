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
        Schema::create('season_student_periods', function (Blueprint $table) {
            /* カラム */
            $table->increments('season_s_period_id')->comment('生徒連絡コマID');
            $table->unsignedInteger('season_student_id')->comment('生徒連絡ID');
            $table->date('lesson_date')->comment('授業日');
            $table->unsignedSmallInteger('period_no')->comment('時限');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['season_student_id','lesson_date','period_no'],'season_student_periods_UNIQUE');

            /* テーブル名コメント */
            $table->comment('特別期間講習 生徒連絡コマ情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('season_student_periods');
    }
};
