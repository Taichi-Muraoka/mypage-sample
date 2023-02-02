<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtRegularDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_regular_detail', function (Blueprint $table) {
            /*カラム*/
            $table->string('roomcd', 4)->comment('教室コード');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->decimal('r_seq', 9, 0)->comment('規定情報連番');
            $table->decimal('rd_seq', 9, 0)->comment('規定情報明細連番');
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->string('weekdaycd', 1)->comment('曜日コード');
            $table->time('start_time', 0)->comment('授業開始時刻');
            $table->decimal('r_minutes', 3, 0)->comment('授業時間数（分）');
            $table->time('end_time', 0)->comment('終了時刻');
            $table->decimal('r_count', 1, 0)->comment('回数');
            $table->string('curriculumcd', 3)->comment('教科コード');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['roomcd','sid','r_seq','rd_seq']);

            /*外部キー*/
            // $table->foreign(['roomcd','sid','r_seq'],'regular_detail_room_sid_r_seq')->references(['roomcd','sid','r_seq'])->on('ext_regular');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_regular_detail');
    }
}
