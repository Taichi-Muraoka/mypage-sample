<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('card_id')->autoIncrement()->comment('ギフトカードID');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->text('card_name')->comment('ギフトカード名');
            $table->text('discount')->comment('割引内容');
            $table->date('grant_time')->default('1000-01-01')->comment('付与日');
            $table->date('term_start')->comment('使用期間開始日');
            $table->date('term_end')->comment('使用期間終了日');
            $table->unsignedTinyInteger('card_state')->comment('ギフトカード状態（0:未使用、1:申請中、2:使用受付、3:使用済）');
            $table->date('apply_time')->nullable()->comment('使用申請日');
            $table->text('comment')->nullable()->comment('事務局コメント');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('sid','card_sid_idx');

            /*外部キー*/
            // $table->foreign('sid','card_sid')->references('sid')->on('ext_student');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card');
    }
}
