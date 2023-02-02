<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodeMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('code_master', function (Blueprint $table) {
            /*カラム*/
            $table->unsignedSmallInteger('data_type')->comment('種別');
            $table->unsignedSmallInteger('code')->comment('コード');
            $table->unsignedSmallInteger('sub_code')->comment('サブコード');
            $table->unsignedSmallInteger('order_code')->comment('表示順');
            $table->text('gen_item1')->nullable()->comment('汎用項目1');
            $table->text('gen_item2')->nullable()->comment('汎用項目2');
            $table->text('gen_item3')->nullable()->comment('汎用項目3');
            $table->text('name')->comment('名称');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['data_type','code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('code_master');
    }
}
