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
        Schema::create('student_entrance_exams', function (Blueprint $table) {
            /* カラム */
            $table->increments('student_exam_id')->comment('生徒所属ID');
            $table->unsignedSmallInteger('student_id')->comment('生徒ID');
            $table->string('school_cd', 13)->comment('学校コード');
            $table->string('department_name', 50)->comment('学部学科名');
            $table->unsignedSmallInteger('priority_no')->comment('志望順');
            $table->string('exam_year', 4)->comment('受験年度');
            $table->string('exam_name', 50)->nullable()->comment('受験日程名');
            $table->date('exam_date')->comment('受験日');
            $table->unsignedSmallInteger('result')->comment('合否');
            $table->text('memo')->nullable()->comment('備考');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            

            /* テーブル名コメント */
            $table->comment('生徒受験情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_entrance_exams');
    }
};
