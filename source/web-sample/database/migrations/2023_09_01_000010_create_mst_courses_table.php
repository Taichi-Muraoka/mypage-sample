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
        Schema::create('mst_courses', function (Blueprint $table) {
            /* カラム */
            $table->string('course_cd', 5)->comment('コースコード');
            $table->string('name', 50)->comment('名称');
            $table->string('short_name', 10)->comment('略称');
            $table->unsignedSmallInteger('course_kind')->comment('コース種別（1:授業単、2:授業複、3:その他）');
            $table->unsignedSmallInteger('summary_kind')->default(0)->comment('給与集計種別');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('course_cd');

            /* テーブル名コメント */
            $table->comment('コースマスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_courses');
    }
};
