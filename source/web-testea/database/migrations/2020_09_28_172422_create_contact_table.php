<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('contact_id')->autoIncrement()->comment('問い合わせID');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->text('title')->comment('タイトル');
            $table->text('text')->comment('本文');
            $table->string('roomcd', 4)->comment('教室コード（問い合わせ先教室）');
            $table->date('regist_time')->default('1000-01-01')->comment('登録日');
            $table->unsignedTinyInteger('contact_state')->comment('0:未回答、1:回答済');
            $table->bigInteger('adm_id')->nullable()->comment('事務局ID（回答者）');
            $table->text('answer_text')->nullable()->comment('回答');
            $table->date('answer_time')->nullable()->comment('回答日');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('sid','contact_sid_idx');

            /*外部キー*/
            // $table->foreign('sid','contact_sid')->references('sid')->on('ext_student');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact');
    }
}
