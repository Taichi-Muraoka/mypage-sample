<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('event_id')->autoIncrement()->comment('イベントID');
            $table->text('name')->comment('イベント名');
            $table->text('cls_cd')->comment('学年');
            $table->date('event_date')->comment('開催日');
            $table->time('start_time', 0)->comment('開始時刻');
            $table->time('end_time', 0)->comment('終了時刻');
            $table->timestamps();
            $table->softDeletes();

            /*外部キー*/
            // $table->foreign('event_id','event_apply_event_id')->references('event_id')->on('event_apply');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event');
    }
}
