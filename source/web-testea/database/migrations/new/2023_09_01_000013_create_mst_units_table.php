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
        Schema::create('mst_units', function (Blueprint $table) {
            /* カラム */
            $table->increments('unit_id', 7)->comment('単元ID');
            $table->string('unit_category_cd', 7)->comment('単元分類コード');
            $table->string('unit_cd', 7)->comment('単元コード');
            $table->string('name', 50)->comment('名称');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            

            /* テーブル名コメント */
            $table->comment('授業単元マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_units');
    }
};
