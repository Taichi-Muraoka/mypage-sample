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
        Schema::create('season_mng', function (Blueprint $table) {
            /* カラム */
            $table->increments('season_mng_id')->comment('特別期間管理ID');
            $table->string('season_cd', 6)->comment('特別期間コード');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->date('t_start_date')->comment('講師受付開始日');
            $table->date('t_end_date')->comment('講師受付終了日');
            $table->date('s_start_date')->comment('生徒受付開始日');
            $table->date('s_end_date')->comment('生徒受付終了日');
            $table->unsignedSmallInteger('lesson_times')->comment('受講回数目安');
            $table->date('confirm_date')->comment('確定日');
            $table->unsignedSmallInteger('status')->default(0)->comment('処理状態（0:未確定、1:確定済）');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['season_cd','campus_cd'],'season_mng_UNIQUE');

            /* テーブル名コメント */
            $table->comment('特別期間講習管理');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('season_mng');
    }
};
