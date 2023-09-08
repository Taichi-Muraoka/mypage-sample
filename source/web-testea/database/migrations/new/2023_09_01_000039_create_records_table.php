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
        Schema::create('records', function (Blueprint $table) {
            /* カラム */
            $table->increments('record_id')->comment('連絡記録ID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->unsignedSmallInteger('record_kind')->comment('記録種別（1:面談、2:電話、3:退会、4:その他）');
            $table->date('received_date')->comment('対応日');
            $table->time('received_time')->comment('対応時刻');
            $table->timestamps('registered_at')->comment('登録日時');
            $table->unsignedInteger('adm_id')->comment('担当者ID');
            $table->text('memo')->comment('内容');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
           
            /* テーブル名コメント */
            $table->comment('連絡記録情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('records');
    }
};
