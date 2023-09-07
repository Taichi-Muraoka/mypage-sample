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
        Schema::create('season_student_requests', function (Blueprint $table) {
            /* カラム */
            $table->increments('season_student_id')->comment('生徒連絡ID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->string('season_cd', 6)->comment('特別期間コード');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->date('apply_date')->nullable()->comment('連絡日');
            $table->text('comment')->nullable()->comment('コメント');
            $table->unsignedSmallInteger('regist_status')->default(0)->comment('生徒登録状態（0:未登録、1:登録済）');
            $table->unsignedSmallInteger('plan_status')->default(0)->comment('コマ組み状態（0:未対応、1:対応中、2:対応済）');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('特別期間講習 生徒連絡情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('season_student_requests');
    }
};
