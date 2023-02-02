<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtExtraIndividualTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_extra_individual', function (Blueprint $table) {
            /*カラム*/
            $table->string('roomcd', 4)->comment('教室コード');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->decimal('i_seq', 9, 0)->comment('個別講習連番');
            $table->string('name', 60)->nullable()->comment('講習名');
            $table->string('symbol', 4)->nullable()->comment('スケジュール表示用シンボル');
            $table->decimal('price', 6, 0)->nullable()->comment('講習料');
            $table->date('bill_plan')->nullable()->comment('請求予定日');
            $table->date('bill_date')->nullable()->comment('請求日');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['roomcd','sid','i_seq']);
            $table->index('sid','extra_individual_sid_idx');

            /*外部キー*/
            // $table->foreign('sid','extra_individual_sid')->references('sid')->on('ext_student');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_extra_individual');
    }
}
