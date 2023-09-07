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
        Schema::create('mst_grades', function (Blueprint $table) {
            /* カラム */
            $table->unsignedSmallInteger('grade_cd')->comment('学年コード');
            $table->unsignedSmallInteger('school_kind')->comment('学校区分（1:小、2:中、3:高、4:その他）');
            $table->string('name', 30)->comment('名称');
            $table->string('short_name', 10)->comment('略称');
            $table->unsignedSmallInteger('age')->default(0)->comment('年齢');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('grade_cd');

            /* テーブル名コメント */
            $table->comment('学年マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_grades');
    }
};
