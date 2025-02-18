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
        Schema::create('mst_unit_categories', function (Blueprint $table) {
            /* カラム */
            $table->string('unit_category_cd', 7)->comment('単元分類コード');
            $table->unsignedSmallInteger('grade_cd')->comment('学年コード');
            $table->string('t_subject_cd', 3)->comment('教材科目コード');
            $table->string('name', 50)->comment('名称');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('unit_category_cd');

            /* テーブル名コメント */
            $table->comment('授業単元分類マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_unit_categories');
    }
};
