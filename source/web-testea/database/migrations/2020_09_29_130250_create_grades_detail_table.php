<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradesDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grades_detail', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('grades_id')->comment('生徒成績ID');
            $table->decimal('grades_seq', 8, 0)->comment('生徒成績通番');
            $table->text('curriculum_name')->comment('教科名');
            $table->string('curriculumcd', 3)->nullable()->comment('教科コード（汎用マスタより選択した教科の場合に使用）');
            $table->decimal('score', 3, 0)->comment('得点');
            $table->tinyInteger('previoustime')->comment('前回比（0:同じ、1:アップ、2:ダウン）');
            $table->decimal('average', 3, 0)->comment('平均点');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['grades_id','grades_seq']);

            /*外部キー*/
            // $table->foreign('grades_id','grades_detail_grades_id')->references('grades_id')->on('grades');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grades_detail');
    }
}
