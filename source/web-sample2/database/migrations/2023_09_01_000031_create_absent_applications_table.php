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
        Schema::create('absent_applications', function (Blueprint $table) {
            /* カラム */
            $table->increments('absent_apply_id')->comment('欠席申請ID');
            $table->unsignedBigInteger('schedule_id')->comment('スケジュールID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->text('absent_reason')->comment('欠席理由');
            $table->unsignedSmallInteger('status')->comment('状態（0:未対応、1:対応済）');
            $table->date('apply_date')->default('1000-01-01')->comment('申請日');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
           
            /* テーブル名コメント */
            $table->comment('欠席申請情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absent_applications');
    }
};
