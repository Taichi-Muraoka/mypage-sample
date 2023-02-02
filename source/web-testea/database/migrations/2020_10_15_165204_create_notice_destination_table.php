<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticeDestinationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_destination', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('notice_id')->comment('お知らせID');
            $table->decimal('destination_seq', 8, 0)->comment('宛先連番');
            $table->smallInteger('destination_type')->comment('宛先種別（1:グループ一斉、2:個別（生徒）、3:個別（教師））');
            $table->decimal('sid', 8, 0)->nullable()->comment('生徒No.');
            $table->decimal('tid', 6, 0)->nullable()->comment('教師No.');
            $table->integer('notice_group_id')->nullable()->comment('お知らせグループID');
            $table->string('roomcd',4)->nullable()->comment('教室コード');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['notice_id', 'destination_seq']);

            /*外部キー*/
            // $table->foreign('notice_id','mypage_temp.notice_destination_notice_id')->references('notice_id')->on('notice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice_destination');
    }
}
