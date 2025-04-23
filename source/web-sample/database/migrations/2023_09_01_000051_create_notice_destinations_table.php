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
        Schema::create('notice_destinations', function (Blueprint $table) {
            /* カラム */
            $table->unsignedInteger('notice_id')->comment('お知らせID');
            $table->unsignedInteger('destination_seq')->comment('宛先連番');
            $table->unsignedSmallInteger('destination_type')->comment('宛先種別（1:グループ一斉、2:個別（生徒）、3:個別（講師）、4:個別（保護者））');
            $table->unsignedInteger('student_id')->nullable()->comment('生徒ID');
            $table->unsignedInteger('tutor_id')->nullable()->comment('講師ID');
            $table->unsignedInteger('notice_group_id')->nullable()->comment('お知らせグループID');
            $table->string('campus_cd', 2)->nullable()->comment('校舎コード');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['notice_id', 'destination_seq']);

            /* テーブル名コメント */
            $table->comment('お知らせ宛先情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice_destinations');
    }
};
