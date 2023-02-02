<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_apply', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('transfer_apply_id')->autoIncrement()->comment('振替連絡ID');
            $table->integer('id')->comment('スケジュールID');
            $table->date('transfer_date')->comment('振替日');
            $table->time('transfer_time', 0)->comment('振替開始時刻');
            $table->text('transfer_reason')->comment('振替理由');
            $table->unsignedTinyInteger('state')->default(0)->comment('状態（0:未対応、1:対応済）');
            $table->date('apply_time')->default('1000-01-01')->comment('申請日');
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
        Schema::dropIfExists('transfer_apply');
    }
}
