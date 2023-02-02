<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtRoomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_room', function (Blueprint $table) {
            /*カラム*/
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->string('roomcd', 4)->comment('教室コード');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['sid','roomcd']);

            /*外部キー*/
            // $table->foreign('sid')->references('sid')->on('ext_student');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_room');
    }
}
