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
        Schema::create('tutor_subjects', function (Blueprint $table) {
            /* カラム */
            $table->increments('tutor_subject_id')->comment('講師担当科目ID');
            $table->unsignedInteger('tutor_id')->comment('講師ID');
            $table->string('subject_cd', 3)->comment('科目コード');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['tutor_id', 'subject_cd'],'tutor_subjects_UNIQUE');

            /* テーブル名コメント */
            $table->comment('講師担当科目情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tutor_subjects');
    }
};
