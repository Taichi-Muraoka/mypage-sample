<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account', function (Blueprint $table) {
            /*カラム*/
            $table->integer('account_id')->comment('アカウントID（生徒No.、教師No.、事務局IDのいずれか）');
            $table->unsignedSmallInteger('account_type')->comment('アカウント種類（生徒、教師、事務局）');
            $table->string('email', 120)->comment('メールアドレス');
            $table->text('password')->comment('パスワード');
            $table->tinyInteger('password_reset')->comment('パスワード再設定（0:不要、1:必要）');
            $table->string('remember_token',100)->nullable()->comment('リメンバートークン');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['account_id','account_type']);
            $table->unique('email','email_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account');
    }
}
