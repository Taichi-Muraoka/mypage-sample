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
        Schema::create('conference_dates', function (Blueprint $table) {
            /* カラム */
            $table->increments('conference_date_id')->comment('面談希望日程ID');
            $table->unsignedInteger('conference_id')->comment('面談連絡ID');
            $table->unsignedSmallInteger('request_no')->comment('希望順');
            $table->date('conference_date')->default('1000-01-01')->comment('面談日');
            $table->time('start_time')->comment('開始時刻');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
           
            /* テーブル名コメント */
            $table->comment('面談日程情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conference_dates');
    }
};
