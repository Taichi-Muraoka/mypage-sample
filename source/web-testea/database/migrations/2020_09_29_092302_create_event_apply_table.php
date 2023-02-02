<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_apply', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('event_apply_id')->autoIncrement()->comment('イベント申込ID');
            $table->bigInteger('event_id')->comment('イベントID');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->unsignedTinyInteger('changes_state')->default(0)->comment('変更状態（0:未対応、1:受付、2:対応済）');
            $table->date('apply_time')->default('1000-01-01')->comment('申込日');
            $table->decimal('members',2 ,0)->comment('参加人数');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('event_id','event_apply_event_id_idx');
            $table->index('sid','event_apply_sid_idx');

            /*外部キー*/
            // $table->foreign('event_id','event_apply_event_id')->references('event_id')->on('event');
            // $table->foreign('sid','event_apply_sid')->references('sid')->on('ext_student');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_apply');
    }
}
