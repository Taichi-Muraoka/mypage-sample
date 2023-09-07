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
        Schema::create('mst_grade_subjects', function (Blueprint $table) {
            /* カラム */
            $table->string('g_subject_cd', 3)->comment('成績科目コード');
            $table->unsignedSmallInteger('school_kind')->comment('学校区分（1:小、2:中、3:高）');
            $table->string('name', 30)->comment('名称');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('g_subject_cd');

            /* テーブル名コメント */
            $table->comment('成績科目マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_grade_subjects');
    }
};
