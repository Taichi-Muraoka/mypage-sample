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
        Schema::create('transfer_applications', function (Blueprint $table) {
            /* カラム */
            $table->increments('transfer_apply_id')->comment('振替依頼ID');
            $table->unsignedSmallInteger('apply_kind')->comment('申請者種別（1:生徒、2:講師）');
            $table->unsignedBigInteger('schedule_id')->comment('スケジュールID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->unsignedInteger('tutor_id')->comment('講師ID');
            $table->text('transfer_reason')->comment('振替理由');
            $table->date('apply_date')->default('1000-01-01')->comment('依頼日');
            $table->unsignedSmallInteger('monthly_count')->default(0)->comment('当月依頼回数');
            $table->unsignedSmallInteger('approval_status')->default(0)->comment('承認状態（0:管理者承認待ち、1:承認待ち、2:承認、3:差戻し日程不都合、4:差戻し代講希望、5:管理者対応済）');
            $table->unsignedInteger('confirm_date_id')->nullable()->comment('確定振替日程ID');
            $table->text('comment')->nullable()->comment('コメント');
            $table->unsignedBigInteger('transfer_schedule_id')->nullable()->comment('振替スケジュールID');
            $table->unsignedSmallInteger('transfer_kind')->nullable()->comment('振替代講区分（1:振替、2:代講）');
            $table->unsignedInteger('substitute_tutor_id')->nullable()->comment('代講講師ID');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
           
            /* テーブル名コメント */
            $table->comment('振替依頼情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfer_applications');
    }
};
