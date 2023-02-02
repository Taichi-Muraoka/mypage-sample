<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grades', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('grades_id')->autoIncrement()->comment('生徒成績ID');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->smallInteger('exam_type')->comment('試験種別（1:模試、2:定期考査）');
            $table->integer('exam_id')->comment('試験ID（模試IDもしくは定期考査のコードマスタの値）');
            $table->text('student_comment')->comment('生徒コメント');
            $table->date('regist_time')->default('1000-01-01')->comment('登録日');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('sid','grades_sid_idx');

            /*外部キー*/
            // $table->foreign('sid','grades_sid')->references('sid')->on('ext_student');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grades');
    }
}
