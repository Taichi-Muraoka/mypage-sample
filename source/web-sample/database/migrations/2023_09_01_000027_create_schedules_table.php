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
        Schema::create('schedules', function (Blueprint $table) {
            /* カラム */
            $table->bigIncrements('schedule_id')->comment('スケジュールID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->date('target_date')->comment('日付');
            $table->unsignedSmallInteger('period_no')->nullable()->comment('時限');
            $table->time('start_time', 0)->comment('開始時刻');
            $table->time('end_time', 0)->comment('終了時刻');
            $table->unsignedSmallInteger('minutes')->comment('時間（分）');
            $table->string('booth_cd', 3)->comment('ブースコード');
            $table->string('course_cd', 5)->comment('コースコード');
            $table->unsignedInteger('student_id')->nullable()->comment('生徒ID');
            $table->unsignedInteger('tutor_id')->nullable()->comment('講師ID');
            $table->string('subject_cd', 3)->nullable()->comment('科目コード');
            $table->unsignedSmallInteger('create_kind')->nullable()->default(1)->comment('データ作成種別（0:一括、1:個別、2:振替）');
            $table->unsignedSmallInteger('lesson_kind')->nullable()->default(0)->comment('授業区分');
            $table->unsignedSmallInteger('how_to_kind')->nullable()->default(0)->comment('通塾種別（0:両者通塾、1:生徒オンライン、2:両者オンライン、3:講師オンライン、4:家庭教師）');
            $table->unsignedSmallInteger('substitute_kind')->nullable()->default(0)->comment('授業代講種別（0:なし、1:代講、2:緊急代講）');
            $table->unsignedInteger('absent_tutor_id')->nullable()->comment('欠席講師ID');
            $table->unsignedSmallInteger('absent_status')->nullable()->default(0)->comment('出欠ステータス');
            $table->unsignedSmallInteger('tentative_status')->nullable()->default(0)->comment('仮登録状態（0:本登録、1:仮登録）');
            $table->unsignedBigInteger('regular_class_id')->nullable()->comment('レギュラー授業ID');
            $table->unsignedInteger('transfer_id')->nullable()->comment('振替依頼ID');
            $table->unsignedBigInteger('transfer_class_id')->nullable()->comment('振替元授業ID');
            $table->unsignedBigInteger('report_id')->nullable()->comment('授業報告書ID');
            $table->text('memo')->nullable()->comment('メモ');
            $table->unsignedInteger('adm_id')->nullable()->comment('登録者ID');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('スケジュール情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};
