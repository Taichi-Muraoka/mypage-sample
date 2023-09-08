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
        Schema::create('student_campuses', function (Blueprint $table) {
            /* カラム */
            $table->increments('student_campuses_id')->comment('生徒所属ID');
            $table->unsignedSmallInteger('student_id')->comment('生徒ID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['student_id', 'campus_cd'], 'student_campuses_UNIQUE');

            /* テーブル名コメント */
            $table->comment('生徒所属情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_campuses');
    }
};
