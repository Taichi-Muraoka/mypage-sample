<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyShiftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_shift', function (Blueprint $table) {
            /*カラム*/
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->string('weekdaycd', 1)->comment('曜日コード');
            $table->time('start_time', 0)->comment('開始時間');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['tid','weekdaycd','start_time']);

            /*外部キー*/
            // $table->foreign('tid','weekly_shift_tid')->references('tid')->on('ext_teacher');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('weekly_shift');
    }
}
