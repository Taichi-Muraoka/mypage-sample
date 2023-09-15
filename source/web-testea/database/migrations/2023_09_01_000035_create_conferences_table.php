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
        Schema::create('conferences', function (Blueprint $table) {
            /* カラム */
            $table->increments('conference_id')->comment('面談連絡ID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->text('comment')->nullable()->comment('特記事項');
            $table->unsignedSmallInteger('status')->comment('状態（0:未登録、1:登録済）');
            $table->date('apply_date')->default('1000-01-01')->comment('連絡日');
            $table->date('conference_date')->nullable()->comment('確定面談日');
            $table->time('start_time')->nullable()->comment('確定面談開始時刻');
            $table->time('end_time')->nullable()->comment('確定面談終了時刻');
            $table->unsignedInteger('conference_schedule_id')->nullable()->comment('面談スケジュールID');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
           
            /* テーブル名コメント */
            $table->comment('面談連絡情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conferences');
    }
};
