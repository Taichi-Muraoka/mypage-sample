<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomHolidayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_holiday', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('room_holiday_id')->autoIncrement()->comment('教室休業日ID');
            $table->string('roomcd',4)->comment('教室コード');
            $table->date('holiday_date')->comment('休業日');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room_holiday');
    }
}
