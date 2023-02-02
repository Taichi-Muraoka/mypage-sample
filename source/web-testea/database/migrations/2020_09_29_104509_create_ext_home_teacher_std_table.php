<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtHomeTeacherStdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_home_teacher_std', function (Blueprint $table) {
            /*カラム*/
            $table->string('roomcd', 4)->comment('教室コード');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->decimal('std_seq', 9, 0)->comment('家庭教師標準連番');
            $table->date('startdate')->comment('開始日');
            $table->date('enddate')->comment('終了日');
            $table->string('std_summary', 100)->comment('標準内容概略');
            $table->decimal('tuition', 8, 0)->comment('月額授業料');
            $table->decimal('expenses', 8, 0)->comment('月額交通費');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['roomcd','sid','std_seq']);
            $table->index('sid', 'home_teacher_std_student_idx');

            /*外部キー*/
            // $table->foreign(sid','mypage_temp.home_teacher_std_sid')->references(sid')->on('mypage_temp.ext_student_kihon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_home_teacher_std');
    }
}
