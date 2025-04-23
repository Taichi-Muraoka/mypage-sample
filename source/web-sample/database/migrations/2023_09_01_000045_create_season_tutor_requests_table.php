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
        Schema::create('season_tutor_requests', function (Blueprint $table) {
            /* カラム */
            $table->increments('season_tutor_id')->comment('講師連絡ID');
            $table->unsignedInteger('tutor_id')->comment('講師ID');
            $table->string('season_cd', 6)->comment('特別期間コード');
            $table->date('apply_date')->comment('連絡日');
            $table->text('comment')->nullable()->comment('コメント');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('特別期間講習 講師連絡情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('season_tutor_requests');
    }
};
