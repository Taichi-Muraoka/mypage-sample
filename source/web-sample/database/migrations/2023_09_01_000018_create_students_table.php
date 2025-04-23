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
        Schema::create('students', function (Blueprint $table) {
            /* カラム */
            $table->increments('student_id')->comment('生徒ID');
            $table->string('name', 50)->comment('名前');
            $table->string('name_kana', 50)->comment('名前かな');
            $table->unsignedSmallInteger('grade_cd')->comment('学年コード');
            $table->string('grade_year', 4)->comment('学年設定年度');
            $table->date('birth_date')->comment('生年月日');
            $table->string('school_cd_e', 13)->nullable()->comment('所属学校コード（小）');
            $table->string('school_cd_j', 13)->nullable()->comment('所属学校コード（中）');
            $table->string('school_cd_h', 13)->nullable()->comment('所属学校コード（高）');
            $table->unsignedSmallInteger('is_jukensei')->comment('受験生フラグ（0:非受験生、1:受験生）');
            $table->string('tel_stu', 20)->nullable()->comment('生徒電話番号');
            $table->string('tel_par', 20)->comment('保護者電話番号');
            $table->string('email_stu', 100)->nullable()->comment('生徒メールアドレス');
            $table->string('email_par', 100)->nullable()->comment('保護者メールアドレス');
            $table->unsignedSmallInteger('login_kind')->nullable()->comment('ログインID種別（1:生徒、2:保護者）');
            $table->unsignedSmallInteger('stu_status')->comment('会員ステータス（0:見込み客、1:在籍、2:休塾予定、3:休塾、4:退会処理中、5:退会済）');
            $table->date('enter_date')->nullable()->comment('入会日');
            $table->date('leave_date')->nullable()->comment('退会日');
            $table->date('recess_start_date')->nullable()->comment('休塾開始日');
            $table->date('recess_end_date')->nullable()->comment('休塾終了日');
            $table->unsignedInteger('past_enter_term')->default(0)->comment('過去通塾期間');
            $table->string('lead_id', 9)->nullable()->comment('顧客ID');
            $table->text('storage_link')->nullable()->comment('ストレージリンク');
            $table->text('memo')->nullable()->comment('メモ');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('生徒情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
};
