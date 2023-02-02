<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimesReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('times_report', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('times_report_id')->autoIncrement()->comment('回数報告書ID');
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->date('report_date')->comment('報告月');
            $table->string('roomcd',4)->comment('教室コード');
            $table->date('regist_time')->default('1000-01-01')->comment('登録日');
            $table->text('office_work')->nullable()->comment('事務作業');
            $table->text('other')->nullable()->comment('その他');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('tid','times_report_tid_idx');

            /*外部キー*/
            // $table->foreign('tid','times_report_tid')->references('tid')->on('ext_teacher');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('times_report');
    }
}
