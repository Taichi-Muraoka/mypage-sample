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
        Schema::create('mst_texts', function (Blueprint $table) {
            /* カラム */
            $table->string('text_cd', 8)->comment('教材コード');
            $table->string('l_subject_cd', 3)->comment('授業科目コード');
            $table->unsignedSmallInteger('grade_cd')->comment('学年コード');
            $table->string('t_subject_cd', 3)->comment('教材科目コード');
            $table->string('name', 50)->comment('名称');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('text_cd');

            /* テーブル名コメント */
            $table->comment('授業教材マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_texts');
    }
};
