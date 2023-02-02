<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtHomeTeacherStdDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_home_teacher_std_detail', function (Blueprint $table) {
            /*カラム*/
            $table->string('roomcd', 4)->comment('教室コード');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->decimal('std_seq', 9, 0)->comment('家庭教師標準連番');
            $table->decimal('std_dtl_seq', 9, 0)->comment('家庭教師標準明細枝番');
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->decimal('std_minutes', 3, 0)->comment('授業時間（分）');
            $table->decimal('std_count', 2, 0)->comment('回数');
            $table->decimal('hour_payment', 8, 0)->comment('時給');
            $table->decimal('roundtrip_expenses', 8, 0)->comment('往復交通費');
            $table->decimal('rem_count', 2, 0)->nullable()->comment('残回数');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['roomcd','sid','std_seq','std_dtl_seq'], 'ext_home_teacher_std_detail_roomcd_sid_std_seq_std_dtl_seq');

            /*外部キー*/
            // $table->foreign(['roomcd','sid','std_seq'],'home_teacher_std_detail_roomcd_sid_std_seq')->references(['roomcd','sid','std_seq'])->on('ext_home_teacher_std');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_home_teacher_std_detail');
    }
}
