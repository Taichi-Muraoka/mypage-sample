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
        Schema::create('mst_subjects', function (Blueprint $table) {
            /* カラム */
            $table->string('subject_cd', 3)->comment('科目コード');
            $table->string('name', 30)->comment('名称');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('subject_cd');

            /* テーブル名コメント */
            $table->comment('授業科目マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_subjects');
    }
};
