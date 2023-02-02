<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('notice_id')->autoIncrement()->comment('お知らせID');
            $table->text('title')->comment('タイトル');
            $table->text('text')->comment('本文');
            $table->smallInteger('notice_type')->comment('お知らせ種別（1:模試、2:イベント、3:短期個別講習、4:その他）');
            $table->bigInteger('tmid_event_id')->nullable()->comment('模試・イベントID（模試IDもしくはイベントID）');
            $table->bigInteger('adm_id')->comment('事務局ID（送信者）');
            $table->string('roomcd', 4)->comment('教室コード（送信元教室）');
            $table->timestamp('regist_time', 0)->useCurrent()->comment('登録日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('adm_id', 'notice_adm_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice');
    }
}
