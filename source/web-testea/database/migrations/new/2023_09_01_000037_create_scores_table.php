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
        Schema::create('scores', function (Blueprint $table) {
            /* カラム */
            $table->increments('score_id')->comment('生徒成績ID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->unsignedSmallInteger('exam_type')->comment('種別（1:模試、2:定期考査、3:評定値）');
            $table->unsignedSmallInteger('regular_exam_cd')->nullable()->comment('定期考査コード');
            $table->string('practice_exam_name', 50)->nullable()->comment('模擬試験名');
            $table->unsignedSmallInteger('term_cd')->nullable()->comment('学期コード');
            $table->unsignedSmallInteger('grade_cd')->nullable()->comment('学年コード');
            $table->date('exam_date')->nullable()->comment('試験日（開始日）');
            $table->text('student_comment')->comment('生徒コメント');
            $table->date('regist_date')->default('1000-01-01')->comment('登録日');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->index('sid','grades_sid_idx');
           
            /* テーブル名コメント */
            $table->comment('生徒成績情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scores');
    }
};
