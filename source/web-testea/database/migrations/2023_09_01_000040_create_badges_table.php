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
        Schema::create('badges', function (Blueprint $table) {
            /* カラム */
            $table->increments('badge_id')->comment('付与バッジID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->unsignedSmallInteger('badge_type')->comment('バッジ種別（1:紹介、2:通塾、3:成績、4:その他）');
            $table->string('reason', 30)->nullable()->comment('認定理由');
            $table->date('authorization_date')->default('1000-01-01')->comment('認定日');
            $table->unsignedInteger('adm_id')->comment('管理者ID');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('バッジ付与情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('badges');
    }
};
