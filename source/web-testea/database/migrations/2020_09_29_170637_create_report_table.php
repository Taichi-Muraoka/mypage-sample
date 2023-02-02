<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('report_id')->autoIncrement()->comment('授業報告書ID');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->date('lesson_date')->comment('授業日（個別教室の場合、スケジュールから）');
            $table->time('start_time', 0)->comment('授業開始時刻（個別教室の場合、スケジュールから）');
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->smallInteger('lesson_type')->comment('授業種別（1:個別教室、2:家庭教師）');
            $table->string('roomcd', 4)->comment('教室コード（個別教室の場合、スケジュールから）');
            $table->integer('id')->nullable()->comment('スケジュールID（個別教室の場合）');
            $table->decimal('r_minutes', 3, 0)->comment('授業時間数');
            $table->text('content')->comment('学習内容');
            $table->text('teacher_comment')->comment('教師コメント');
            $table->text('parents_comment')->nullable()->comment('保護者コメント');
            $table->date('regist_time')->default('1000-01-01')->comment('登録日');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('id','report_id_idx');
            $table->index('sid','report_sid_idx');
            $table->index('tid','report_tid_idx');

            /*外部キー*/
            // $table->foreign('id','report_id')->references('id')->on('ext_schedule');
            // $table->foreign('sid','report_sid')->references('sid')->on('ext_student');
            // $table->foreign('tid','report_tid')->references('tid')->on('ext_teacher');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report');
    }
}
