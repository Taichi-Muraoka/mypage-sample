<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTutorRelateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutor_relate', function (Blueprint $table) {
            /*カラム*/
            $table->string('roomcd',4)->comment('教室コード');
            $table->decimal('sid',8 ,0 )->comment('生徒No.');
            $table->decimal('tid',6 ,0)->comment('教師No.');
            $table->timestamps();
            $table->softDeletes();

             /*インデックス*/
             $table->primary(['roomcd','sid','tid']);
             $table->index('sid','tutor_relate_sid_idx');
             $table->index('tid','tutor_relate_tid_idx');

             /*外部キー*/
            //  $table->foreign('sid','mypage_temp.tutor_relate_sid')->references('sid')->on('ext_student_kihon');
            //  $table->foreign('tid','mypage_temp.tutor_relate_tid')->references('tid')->on('ext_teacher');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tutor_relate');
    }
}
