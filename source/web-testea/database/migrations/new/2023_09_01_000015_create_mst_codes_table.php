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
        Schema::create('mst_codes', function (Blueprint $table) {
            /*カラム*/
            $table->increments('code_id')->comment('コードID');
            $table->unsignedSmallInteger('data_type')->comment('種別');
            $table->unsignedSmallInteger('code')->comment('コード');
            $table->unsignedSmallInteger('sub_code')->default(0)->comment('サブコード');
            $table->unsignedSmallInteger('order_code')->comment('表示順');
            $table->string('gen_item1', 100)->nullable()->comment('汎用項目1');
            $table->string('gen_item2', 100)->nullable()->comment('汎用項目2');
            $table->string('gen_item3', 100)->nullable()->comment('汎用項目3');
            $table->string('name', 50)->comment('名称');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->unique(['data_type','code'],'mst_codes_UNIQUE');

            /* テーブル名コメント */
            $table->comment('コードマスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_codes');
    }
};
