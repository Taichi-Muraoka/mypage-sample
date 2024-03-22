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
        Schema::create('reports', function (Blueprint $table) {
            /* カラム */
            $table->bigIncrements('report_id')->comment('授業報告書ID');
            $table->unsignedInteger('tutor_id')->comment('講師ID');
            $table->unsignedBigInteger('schedule_id')->comment('スケジュールID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->string('course_cd', 5)->comment('コースコード');
            $table->date('lesson_date')->comment('授業日');
            $table->unsignedSmallInteger('period_no')->comment('時限');
            $table->unsignedInteger('student_id')->nullable()->comment('生徒ID');
            $table->text('monthly_goal')->nullable()->comment('今月の目標');
            $table->string('test_contents', 100)->nullable()->comment('確認テスト内容');
            $table->unsignedSmallInteger('test_score')->nullable()->comment('確認テスト得点');
            $table->unsignedSmallInteger('test_full_score')->nullable()->comment('確認テスト満点');
            $table->unsignedSmallInteger('achievement')->nullable()->comment('宿題達成度');
            $table->text('goodbad_point')->nullable()->comment('達成・課題点');
            $table->text('solution')->nullable()->comment('解決策');
            $table->text('others_comment')->nullable()->comment('その他');
            $table->unsignedSmallInteger('approval_status')->default(1)->comment('承認状態（1:承認待ち、2:承認、3:差戻し）');
            $table->text('admin_comment')->nullable()->comment('管理者コメント');
            $table->date('regist_date')->default('1000-01-01')->comment('登録日');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('tutor_id','report_INDEX1');

            /* テーブル名コメント */
            $table->comment('授業報告書情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
