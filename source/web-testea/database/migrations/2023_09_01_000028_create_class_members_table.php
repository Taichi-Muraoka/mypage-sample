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
        Schema::create('class_members', function (Blueprint $table) {
            /* カラム */
            $table->increments('class_member_id')->comment('受講生徒情報ID');
            $table->unsignedInteger('schedule_id')->comment('スケジュールID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->unsignedInteger('absent_status')->comment('出欠ステータス');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['schedule_id','student_id'],'class_members_UNIQUE');

            /* テーブル名コメント */
            $table->comment('受講生徒情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('class_members');
    }
};
