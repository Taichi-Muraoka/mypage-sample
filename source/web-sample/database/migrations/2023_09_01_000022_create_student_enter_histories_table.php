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
        Schema::create('student_enter_histories', function (Blueprint $table) {
            /* カラム */
            $table->increments('enter_histories_id')->comment('生徒退会理由ID');
            $table->unsignedSmallInteger('student_id')->comment('生徒ID');
            $table->date('enter_date')->comment('入会日');
            $table->date('leave_date')->comment('退会日');
            $table->unsignedSmallInteger('enter_term')->comment('通塾期間');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            

            /* テーブル名コメント */
            $table->comment('生徒入退会履歴情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_enter_histories');
    }
};
