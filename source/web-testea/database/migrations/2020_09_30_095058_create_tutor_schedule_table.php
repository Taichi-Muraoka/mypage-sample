<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTutorScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutor_schedule', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('tutor_schedule_id')->autoIncrement()->comment('教師スケジュールID');
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->date('start_date')->comment('開催日');
            $table->time('start_time', 0)->comment('開始時刻');
            $table->time('end_time', 0)->comment('終了時刻');
            $table->text('title')->comment('タイトル');
            $table->string('roomcd',4)->comment('教室コード');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('tid','schedule_tutor_tid_idx');

            /*外部キー*/
            // $table->foreign('tid','schedule_tutor_tid')->references('tid')->on('ext_teacher');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tutor_schedule');
    }
}
