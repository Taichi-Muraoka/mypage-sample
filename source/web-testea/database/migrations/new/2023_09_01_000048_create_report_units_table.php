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
        Schema::create('report_units', function (Blueprint $table) {
            /* カラム */
            $table->increments('report_unit_id')->comment('報告書教材単元ID');
            $table->unsignedBigInteger('report_id')->comment('授業報告書ID');
            $table->string('sub_cd', 2)->comment('授業報告書サブコード');
            $table->string('text_cd', 8)->comment('教材コード');
            $table->string('free_text_name', 50)->nullable()->comment('教材名（フリー）');
            $table->string('text_page', 50)->nullable()->comment('教材ページ');
            $table->string('unit_category_cd1', 7)->nullable()->comment('単元分類コード1');
            $table->string('free_category_name1', 50)->nullable()->comment('単元分類名（フリー）1');
            $table->string('unit_cd1', 2)->nullable()->comment('単元コード1');
            $table->string('free_unit_name1', 50)->nullable()->comment('単元名（フリー）1');
            $table->string('unit_category_cd2', 7)->nullable()->comment('単元分類コード2');
            $table->string('free_category_name2', 50)->nullable()->comment('単元分類名（フリー）2');
            $table->string('unit_cd2', 2)->nullable()->comment('単元コード2');
            $table->string('free_unit_name2', 50)->nullable()->comment('単元名（フリー）2');
            $table->string('unit_category_cd3', 7)->nullable()->comment('単元分類コード3');
            $table->string('free_category_name3', 50)->nullable()->comment('単元分類名（フリー）3');
            $table->string('unit_cd3', 2)->nullable()->comment('単元コード3');
            $table->string('free_unit_name3', 50)->nullable()->comment('単元名（フリー）3');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->unique(['report_id','sub_cd'],'report_units_UNIQUE');

            /* テーブル名コメント */
            $table->comment('授業報告書教材単元情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_units');
    }
};
