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
        Schema::create('samples', function (Blueprint $table) {
            /*カラム*/
            $table->increments('sample_id')->comment('サンプルID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->string('sample_title', 50)->comment('サンプルタイトル');
            $table->text('sample_text')->comment('サンプル本文');
            $table->date('regist_date')->default('1000-01-01')->comment('登録日');
            $table->unsignedSmallInteger('sample_state')->comment('サンプルステータス（0:未、1:済）');
            $table->unsignedInteger('adm_id')->nullable()->comment('管理者ID（回答者）');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('student_id','sample_idx1');

            /* テーブル名コメント */
            $table->comment('サンプル情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('samples');
    }
};
