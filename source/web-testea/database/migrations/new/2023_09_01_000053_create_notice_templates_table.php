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
        Schema::create('notice_templates', function (Blueprint $table) {
            /* カラム */
            $table->increments('template_id')->comment('お知らせ定型文ID');
            $table->string('template_name', 50)->comment('定型文名');
            $table->string('title', 50)->comment('タイトル');
            $table->text('text')->comment('本文');
            $table->unsignedSmallInteger('notice_type')->comment('お知らせ種別（4:その他、5:面談、6:特別期間講習、7:成績登録、8:請求、9:給与、10:追加請求）');
            $table->unsignedSmallInteger('order_code')->comment('表示順');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('お知らせ定型文');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice_templates');
    }
};
