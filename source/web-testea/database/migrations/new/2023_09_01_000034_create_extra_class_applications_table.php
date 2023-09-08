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
        Schema::create('extra_class_applications', function (Blueprint $table) {
            /* カラム */
            $table->increments('extra_apply_id')->comment('追加授業依頼ID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->unsignedSmallInteger('status')->comment('状態（0:未対応、1:対応済）');
            $table->unsignedBigInteger('schedule_id')->default(0)->comment('スケジュールID');
            $table->text('request')->comment('依頼内容');
            $table->date('apply_date')->default('1000-01-01')->comment('依頼日');
            $table->text('admin_comment')->nullable()->comment('管理者コメント');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
           
            /* テーブル名コメント */
            $table->comment('追加授業依頼情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extra_class_applications');
    }
};
