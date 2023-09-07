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
        Schema::create('mst_prefectures', function (Blueprint $table) {
            /*カラム*/
            $table->string('prefecture_id')->comment('都道府県番号');
            $table->string('name', 10)->comment('名称');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary('prefecture_id');

            /* テーブル名コメント */
            $table->comment('都道府県マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_prefectures');
    }
};
