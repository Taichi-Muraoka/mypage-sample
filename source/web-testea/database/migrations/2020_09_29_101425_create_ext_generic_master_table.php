<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtGenericMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_generic_master', function (Blueprint $table) {
            /*カラム*/
            $table->string('codecls', 3)->comment('コード区分(固定キー)');
            $table->string('code', 20)->comment('コード(可変キー)');
            $table->string('value1', 10)->nullable()->comment('数値1');
            $table->string('value2', 10)->nullable()->comment('数値2');
            $table->string('value3', 10)->nullable()->comment('数値3');
            $table->string('value4', 10)->nullable()->comment('数値4');
            $table->string('value5', 10)->nullable()->comment('数値5');
            $table->string('name1', 80)->nullable()->comment('名称1');
            $table->string('name2', 80)->nullable()->comment('名称2');
            $table->string('name3', 80)->nullable()->comment('名称3');
            $table->string('name4', 80)->nullable()->comment('名称4');
            $table->string('name5', 80)->nullable()->comment('名称5');
            $table->decimal('disp_order', 4, 0)->nullable()->comment('表示順');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['codecls','code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_generic_master');
    }
}
