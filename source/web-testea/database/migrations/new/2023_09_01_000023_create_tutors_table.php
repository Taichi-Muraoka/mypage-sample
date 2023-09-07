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
        Schema::create('tutors', function (Blueprint $table) {
            /* カラム */
            $table->increments('tutor_id')->comment('講師ID');
            $table->string('name', 50)->comment('名前');
            $table->string('name_kana', 50)->comment('名前かな');
            $table->string('tel', 20)->comment('電話番号');
            $table->string('email', 100)->comment('メールアドレス');
            $table->string('address', 100)->nullable()->comment('住所');
            $table->date('birth_date')->comment('生年月日');
            $table->unsignedSmallInteger('gender_cd')->comment('性別（1:男性、2:女性、9:不明・その他）');
            $table->unsignedSmallInteger('grade_cd')->comment('講師学年コード');
            $table->string('grade_year', 4)->comment('学年設定年度');
            $table->string('school_cd_j', 13)->nullable()->comment('出身中学コード');
            $table->string('school_cd_h', 13)->nullable()->comment('出身高校コード');
            $table->string('school_cd_u', 13)->nullable()->comment('所属大学コード');
            $table->decimal('hourly_base_wage', 4, 0)->default(0)->comment('授業時給（ベース）');
            $table->unsignedSmallInteger('tutor_status')->comment('講師ステータス（1:在籍、2:退職処理中、3:退職済）');
            $table->date('enter_date')->nullable()->comment('勤務開始日');
            $table->date('leave_date')->nullable()->comment('退職日');
            $table->text('memo')->nullable()->comment('メモ');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('講師情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tutors');
    }
};
