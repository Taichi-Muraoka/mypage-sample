<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtExtraIndDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_extra_ind_detail', function (Blueprint $table) {
            /*カラム*/
            $table->string('roomcd', 4)->comment('教室コード');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->decimal('i_seq', 9, 0)->comment('個別講習連番');
            $table->decimal('period_no', 8, 0)->comment('個別講習コマ連番');
            $table->date('extra_date')->comment('講習日');
            $table->string('curriculumcd', 3)->comment('教科コード');
            $table->time('start_time', 0)->useCurrent()->comment('授業開始時刻');
            $table->decimal('r_minutes', 3, 0)->comment('授業時間数（分）');
            $table->time('end_time', 0)->useCurrent()->comment('授業終了時刻');
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['roomcd','sid','i_seq','period_no']);
            $table->unique(['sid','extra_date','start_time'],'ext_extra_ind_detail_sid_extra_date_start_time');
            $table->index('tid','extra_ind_detail_tid_idx');

            /*外部キー*/
            // $table->foreign(['roomcd','sid','i_seq'],'extra_ind_detail_roomcd_sid_i_seq')->references(['roomcd','sid','i_seq'])->on('ext_extra_individual');
            // $table->foreign('tid','extra_ind_detail_tid')->references('tid')->on('ext_teacher');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_extra_ind_detail');
    }
}
