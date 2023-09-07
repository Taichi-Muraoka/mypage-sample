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
        Schema::create('mst_timetables', function (Blueprint $table) {
            /* カラム */
            $table->increments('taimetable_id', 7)->comment('時間割ID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->unsignedSmallInteger('period_no')->comment('時限');
            $table->time('start_time')->comment('開始時刻');
            $table->time('end_time')->comment('終了時刻');
            $table->unsignedSmallInteger('timetable_kind')->comment('時間割区分（0:通常、1:特別期間）');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['campus_cd,period_no,kind_cd'], 'mst_timetables_UNIQUE');

            /* テーブル名コメント */
            $table->comment('時間割マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_timetables');
    }
};
