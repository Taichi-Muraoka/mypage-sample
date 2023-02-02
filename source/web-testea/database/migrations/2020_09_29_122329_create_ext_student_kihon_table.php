<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtStudentKihonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_student_kihon', function (Blueprint $table) {
            /*カラム*/
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->string('name', 50)->comment('名前');
            $table->string('cls_cd', 2)->comment('学年');
            $table->string('mailaddress1', 100)->comment('メールアドレス1');
            $table->date('enter_date')->nullable()->comment('入会日');
            $table->decimal('disp_flg', 1, 0)->comment('表示対象フラグ（0:表示対象外，1:表示対象）');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary('sid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_student_kihon');
    }
}
