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
        Schema::create('season_student_times', function (Blueprint $table) {
            /* カラム */
            $table->increments('season_times_id')->comment('生徒実施回数ID');
            $table->unsignedInteger('season_student_id')->comment('生徒連絡ID');
            $table->string('subject_cd', 3)->comment('科目コード');
            $table->unsignedSmallInteger('times')->comment('回数');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['season_student_id','subject_cd'],'season_student_times_UNIQUE');

            /* テーブル名コメント */
            $table->comment('特別期間講習 生徒実施回数情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('season_student_times');
    }
};
