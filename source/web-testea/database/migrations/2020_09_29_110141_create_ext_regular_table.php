<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtRegularTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_regular', function (Blueprint $table) {
            /*カラム*/
            $table->string('roomcd', 4)->comment('教室コード');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->decimal('r_seq', 9, 0)->comment('規定情報連番');
            $table->date('startdate')->comment('開始日');
            $table->date('enddate')->comment('終了日');
            $table->string('regular_summary', 100)->comment('規定内容概略');
            $table->decimal('tuition', 8, 0)->comment('月額規定授業料');
            $table->decimal('base_tuition', 8, 0)->comment('料金表金額');
            $table->decimal('base_time', 3, 0)->comment('料金表時間');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['roomcd','sid','r_seq']);
            $table->index('sid','regular_student_idx');

            /*外部キー*/
            // $table->foreign('sid','regular_sid')->references('sid')->on('ext_student');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_regular');
    }
}
