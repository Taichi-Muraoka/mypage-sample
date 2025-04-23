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
        Schema::create('contacts', function (Blueprint $table) {
            /*カラム*/
            $table->increments('contact_id')->comment('問い合わせID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->string('title', 50)->comment('タイトル');
            $table->text('text')->comment('本文');
            $table->string('campus_cd', 2)->comment('宛先校舎コード');
            $table->date('regist_time')->default('1000-01-01')->comment('登録日');
            $table->unsignedSmallInteger('contact_state')->comment('回答状態（0:未回答、1:回答済）');
            $table->unsignedInteger('adm_id')->nullable()->comment('管理者ID（回答者）');
            $table->text('answer_text')->nullable()->comment('回答');
            $table->date('answer_time')->nullable()->comment('回答日');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('student_id','contact_sid_idx');

            /* テーブル名コメント */
            $table->comment('問い合わせ情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
};