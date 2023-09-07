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
        Schema::create('notice_groups', function (Blueprint $table) {
            /* カラム */
            $table->unsignedInteger('notice_group_id')->comment('お知らせグループID');
            $table->unsignedSmallInteger('group_type')->comment('グループ種類（1:生徒、2:講師）');
            $table->unsignedSmallInteger('cls_cd')->nullable()->comment('学年');
            $table->unsignedSmallInteger('cls_cd_next')->nullable()->comment('次年度学年');
            $table->string('group_name', 50)->comment('グループ名');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary('notice_group_id');

            /* テーブル名コメント */
            $table->comment('お知らせグループ情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice_groups');
    }
};
