<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_apply', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('change_id')->autoIncrement()->comment('コース変更・授業追加申請ID');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->tinyInteger('change_type')->comment('コース変更種別（1:個別コース追加、2:個別コース変更、等々）');
            $table->text('changes_text')->comment('コース変更・授業追加希望内容');
            $table->date('apply_time')->default('1000-01-01')->comment('申請日');
            $table->unsignedTinyInteger('changes_state')->comment('変更状態（0:未対応、1:受付、2:対応済）');
            $table->text('comment')->nullable()->comment('事務局コメント');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_apply');
    }
}
