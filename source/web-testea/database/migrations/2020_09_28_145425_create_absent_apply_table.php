<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsentApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absent_apply', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('absent_apply_id')->autoIncrement()->comment('欠席申請ID');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->date('lesson_date')->comment('授業日（個別教室の場合、スケジュールから）');
            $table->time('start_time', 0)->comment('授業開始時刻（個別教室の場合、スケジュールから）');
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->unsignedSmallInteger('lesson_type')->comment('授業種別（1:個別教室、2:家庭教師）');
            $table->string('roomcd', 4)->comment('教室コード（個別教室の場合、スケジュールから）');
            $table->integer('id')->nullable()->comment('スケジュールID（個別教室の場合）');
            $table->text('absent_reason')->comment('欠席理由');
            $table->unsignedTinyInteger('state')->comment('状態（0:未対応、1:対応済）');
            $table->date('apply_time')->default('1000-01-01')->comment('申請日');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('id','absent_apply_id_idx');
            $table->index('sid','absent_sid_idx');
            $table->index('tid','absent_tid_idx');

            /*外部キー*/
            // $table->foreign('id','absent_apply_id')->references('id')->on('ext_schedule');
            // $table->foreign('sid','absent_sid')->references('sid')->on('ext_student');
            // $table->foreign('tid','absent_tid')->references('tid')->on('ext_teacher');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absent_apply');
    }
}
