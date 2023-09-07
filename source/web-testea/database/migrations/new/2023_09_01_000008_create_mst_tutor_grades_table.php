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
        Schema::create('mst_tutor_grades', function (Blueprint $table) {
            /* カラム */
            $table->unsignedSmallInteger('grade_cd')->comment('学年コード');
            $table->unsignedSmallInteger('school_kind')->comment('講師学校区分（1:大学、2:大学院、3:その他）');
            $table->string('name', 30)->comment('名称');
            $table->string('short_name', 10)->comment('略称');
            $table->unsignedSmallInteger('auto_update_flg')->default(0)->comment('年次更新可否');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('grade_cd');

            /* テーブル名コメント */
            $table->comment('講師学年マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_tutor_grades');
    }
};
