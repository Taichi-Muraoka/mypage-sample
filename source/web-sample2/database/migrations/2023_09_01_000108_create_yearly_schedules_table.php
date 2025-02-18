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
        Schema::create('yearly_schedules', function (Blueprint $table) {
            /* カラム */
            $table->increments('yearly_schedule_id')->comment('年間予定ID');
            $table->string('school_year', 4)->comment('年度');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->date('lesson_date')->comment('日付');
            $table->unsignedSmallInteger('day_cd')->comment('曜日コード');
            $table->unsignedSmallInteger('date_kind')->comment('期間区分（0:通常、1:特別期間春、2:特別期間夏、3:特別期間冬、9:休日）');
            $table->string('school_month', 2)->comment('月度');
            $table->unsignedSmallInteger('week_count')->comment('週数');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['campus_cd','lesson_date'],'yearly_schedules_UNIQUE');

            /* テーブル名コメント */
            $table->comment('教室年間予定情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yearly_schedules');
    }
};
