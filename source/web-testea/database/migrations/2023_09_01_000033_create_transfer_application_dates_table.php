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
        Schema::create('transfer_application_dates', function (Blueprint $table) {
            /* カラム */
            $table->increments('transfer_date_id')->comment('振替日程ID');
            $table->unsignedInteger('transfer_apply_id')->comment('振替依頼ID');
            $table->unsignedSmallInteger('request_no')->comment('希望順');
            $table->date('transfer_date')->default('1000-01-01')->comment('振替日');
            $table->unsignedSmallInteger('period_no')->comment('時限');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['transfer_apply_id','request_no'],'transfer_apply_no_UNIQUE');
           
            /* テーブル名コメント */
            $table->comment('振替依頼日程情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfer_application_dates');
    }
};
