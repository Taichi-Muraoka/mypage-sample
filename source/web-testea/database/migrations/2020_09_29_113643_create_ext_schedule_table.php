<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_schedule', function (Blueprint $table) {
            /*カラム*/
            $table->integer('id')->comment('スケジュールID');
            $table->string('roomcd', 4)->comment('教室コード');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->string('lesson_type', 1)->comment('授業分類コード');
            $table->string('symbol', 4)->comment('スケジュール表示用シンボル');
            $table->string('curriculumcd', 3)->nullable()->comment('教科コード');
            $table->decimal('rglr_minutes', 3, 0)->nullable()->comment('規定時間数（分）');
            $table->integer('gmid')->nullable()->comment('集団講習ID');
            $table->decimal('period_no', 8, 0)->nullable()->comment('集団講習実施日連番');
            $table->integer('tmid')->nullable()->comment('模試ID');
            $table->decimal('tid', 6, 0)->nullable()->comment('教師No.');
            $table->date('lesson_date')->comment('授業日');
            $table->time('start_time', 0)->nullable()->comment('授業開始時刻');
            $table->decimal('r_minutes', 3, 0)->nullable()->comment('授業時間数（分）');
            $table->time('end_time', 0)->nullable()->comment('授業終了時刻');
            $table->decimal('pre_tid', 6, 0)->nullable()->comment('教師No.（初期設定値）');
            $table->date('pre_lesson_date')->nullable()->comment('授業日（初期設定値）');
            $table->time('pre_start_time', 0)->nullable()->comment('授業開始時刻（初期設定値）');
            $table->decimal('pre_r_minutes', 3, 0)->nullable()->comment('授業時間数（分）（初期設定値）');
            $table->time('pre_end_time', 0)->nullable()->comment('授業終了時刻（初期設定値）');
            $table->string('chg_status_cd', 1)->nullable()->comment('時間変更区分');
            $table->decimal('diff_time', 3, 0)->nullable()->comment('変更授業時間数（分）');
            $table->tinyInteger('substitute_flg')->nullable()->default(0)->comment('代理フラグ');
            $table->string('atd_status_cd', 1)->nullable()->comment('出欠・振替コード');
            $table->string('status_info', 80)->nullable()->comment('出欠補足情報');
            $table->string('create_kind_cd', 1)->comment('作成区分コード');
            $table->string('transefer_kind_cd', 1)->default(0)->comment('振替区分コード');
            $table->date('trn_lesson_date')->nullable()->comment('授業日（後日振替）');
            $table->time('trn_start_time', 0)->nullable()->comment('授業開始時刻（後日振替）');
            $table->decimal('trn_r_minutes', 3, 0)->nullable()->comment('授業時間数（分）（初期設定値）');
            $table->time('trn_end_time', 0)->nullable()->comment('授業終了時刻（後日振替）');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary('id');
            $table->index('sid','schedule_sid_idx');
            $table->index('tid','schedule_tid_idx');
            $table->index('tmid','schedule_tmid_idx');

            /*外部キー*/
            // $table->foreign('pre_tid','schedule_pre_tid')->references('tid')->on('ext_teacher');
            // $table->foreign('sid','schedule_sid')->references('sid')->on('ext_teacher');
            // $table->foreign('tid','schedule_tid')->references('tid')->on('ext_teacher');
            // $table->foreign('tmid','schedule_tmid')->references('tmid')->on('ext_trial_master');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_schedule');
    }
}
