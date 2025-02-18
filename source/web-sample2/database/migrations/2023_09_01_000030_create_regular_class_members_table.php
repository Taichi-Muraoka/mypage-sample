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
        Schema::create('regular_class_members', function (Blueprint $table) {
            /* カラム */
            $table->bigIncrements('regular_members_id')->comment('レギュラー受講生徒情報ID');
            $table->unsignedBigInteger('regular_class_id')->comment('レギュラー授業ID');
            $table->unsignedInteger('student_id')->comment('受講生徒ID');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['regular_class_id','student_id'], 'regular_class_members_UNIQUE');

            /* テーブル名コメント */
            $table->comment('レギュラー受講生徒情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regular_class_members');
    }
};
