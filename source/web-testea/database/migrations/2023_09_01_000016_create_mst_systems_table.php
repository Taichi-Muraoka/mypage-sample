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
        Schema::create('mst_systems', function (Blueprint $table) {
            /*カラム*/
            $table->unsignedSmallInteger('key_id')->comment('システム変数ID');
            $table->string('name', 50)->comment('名称');
            $table->unsignedSmallInteger('datatype_kind')->comment('データ型種別（1:数値、2:文字列、3:日付）');
            $table->integer('value_num')->nullable()->default(0)->comment('値（数値）');
            $table->string('value_str', 50)->nullable()->comment('値（文字列）');
            $table->date('value_date')->nullable()->comment('値（日付）');
            $table->unsignedSmallInteger('change_flg')->default(0)->comment('画面変更可否（0:可、1:不可）');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary('key_id');

            /* テーブル名コメント */
            $table->comment('システムマスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_systems');
    }
};
