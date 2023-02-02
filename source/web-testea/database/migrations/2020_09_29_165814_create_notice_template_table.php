<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticeTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_template', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('template_id')->autoIncrement()->comment('お知らせ定型文ID');
            $table->text('template_name')->comment('定型文名');
            $table->text('title')->comment('タイトル');
            $table->text('text')->comment('本文');
            $table->smallInteger('notice_type')->comment('お知らせ種別（1:模試、2:イベント、3:短期個別講習、4:その他）');
            $table->smallInteger('order_code')->comment('表示順');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice_template');
    }
}
