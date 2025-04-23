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
        Schema::create('tutor_free_periods', function (Blueprint $table) {
            /* カラム */
            $table->increments('free_period_id')->comment('空き時間ID');
            $table->unsignedInteger('tutor_id')->comment('講師ID');
            $table->unsignedInteger('day_cd')->comment('曜日コード');
            $table->unsignedInteger('period_no')->comment('時限');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['tutor_id','day_cd','period_no'],'tutor_free_periods_UNIQUE');

            /* テーブル名コメント */
            $table->comment('講師空き時間情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tutor_free_periods');
    }
};
