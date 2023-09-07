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
        Schema::create('notices', function (Blueprint $table) {
            /* カラム */
            $table->increments('notice_id')->comment('お知らせID');
            $table->string('title', 50)->comment('タイトル');
            $table->text('text')->comment('本文');
            $table->unsignedSmallInteger('notice_type')->comment('お知らせ種別（4:その他、5:面談、6:特別期間講習、7:成績登録、8:請求、9:給与、10:追加請求）');
            $table->unsignedInteger('adm_id')->comment('送信者ID');
            $table->string('campus_cd', 2)->comment('送信元校舎コード');
            $table->timestamp('regist_time')->comment('登録日時');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('お知らせ情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notices');
    }
};
