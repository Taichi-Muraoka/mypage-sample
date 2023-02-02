<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticeGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_group', function (Blueprint $table) {
            /*カラム*/
            $table->integer('notice_group_id')->comment('お知らせグループID');
            $table->unsignedSmallInteger('group_type')->comment('グループ種類（1:生徒、2:教師）');
            $table->string('cls_cd', 2)->nullable()->comment('学年');
            $table->string('cls_cd_next', 2)->nullable()->comment('次年度学年');
            $table->text('group_name')->comment('グループ名');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary('notice_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice_group');
    }
}
