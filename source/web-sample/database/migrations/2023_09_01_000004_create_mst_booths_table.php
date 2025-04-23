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
        Schema::create('mst_booths', function (Blueprint $table) {
            /* カラム */
            $table->increments('booth_id')->comment('ブースID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->string('booth_cd', 3)->comment('ブースコード');
            $table->unsignedSmallInteger('usage_kind')->comment('用途種別（1:授業用、2:オンライン専用、3:面談用、4:両者オンライン、5:家庭教師）');
            $table->string('name', 50)->comment('名称');
            $table->unsignedSmallInteger('disp_order')->comment('表示順');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['campus_cd','booth_cd'],'mst_booths_UNIQUE');

            /* テーブル名コメント */
            $table->comment('ブースマスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_booths');
    }
};
