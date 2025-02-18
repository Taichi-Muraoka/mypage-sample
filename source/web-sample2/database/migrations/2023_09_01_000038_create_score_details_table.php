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
        Schema::create('score_details', function (Blueprint $table) {
            /* カラム */
            $table->increments('score_datail_id')->comment('生徒成績詳細ID');
            $table->unsignedInteger('score_id')->comment('生徒成績ID');
            $table->string('g_subject_cd', 3)->comment('成績科目コード');
            $table->unsignedSmallInteger('score')->comment('得点・評定値');
            $table->unsignedSmallInteger('full_score')->nullable()->comment('満点');
            $table->decimal('average', 5, 1)->nullable()->comment('平均点');
            $table->decimal('deviation_score', 5, 1)->nullable()->comment('偏差値');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['score_id','g_subject_cd'], 'score_details_UNIQUE');
           
            /* テーブル名コメント */
            $table->comment('生徒成績詳細情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('score_details');
    }
};
