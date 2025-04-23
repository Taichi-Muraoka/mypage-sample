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
        Schema::create('regular_classes', function (Blueprint $table) {
            /* カラム */
            $table->bigIncrements('regular_class_id')->comment('レギュラー授業ID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->unsignedSmallInteger('day_cd')->comment('曜日コード');
            $table->unsignedSmallInteger('period_no')->comment('時限');
            $table->time('start_time', 0)->comment('開始時刻');
            $table->time('end_time', 0)->comment('終了時刻');
            $table->unsignedSmallInteger('minutes')->comment('時間（分）');
            $table->string('booth_cd', 3)->comment('ブースコード');
            $table->string('course_cd', 5)->comment('コースコード');
            $table->unsignedInteger('student_id')->nullable()->comment('生徒ID');
            $table->unsignedInteger('tutor_id')->nullable()->comment('講師ID');
            $table->string('subject_cd', 3)->nullable()->comment('科目コード');
            $table->unsignedSmallInteger('how_to_kind')->nullable()->default(0)->comment('通塾種別（0:両者通塾、1:生徒オンライン、2:両者オンライン、3:講師オンライン、4:家庭教師）');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('レギュラー授業情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regular_classes');
    }
};
