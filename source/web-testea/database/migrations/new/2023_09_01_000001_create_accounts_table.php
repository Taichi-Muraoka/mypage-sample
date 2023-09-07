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
        Schema::create('accounts', function (Blueprint $table) {
            /* カラム */
            $table->unsignedInteger('account_id')->comment('アカウントID（生徒No.、教師No.、事務局IDのいずれか）');
            $table->unsignedSmallInteger('account_type')->comment('アカウント種類（生徒、教師、事務局）');
            $table->string('email', 120)->comment('メールアドレス');
            $table->text('password')->comment('パスワード');
            $table->unsignedSmallInteger('password_reset')->default(0)->comment('パスワード再設定（0:不要、1:必要）');
            $table->string('remember_token',100)->nullable()->comment('リメンバートークン');
            $table->unsignedSmallInteger('plan_type')->default(0)->comment('生徒種類（0:通常、1:ハイプラン）');
            $table->unsignedSmallInteger('login_flg')->default(0)->comment('ログイン可否（0:可、1:不可）');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary(['account_id','account_type']);
            $table->unique('email','accounts_UNIQUE');

            /* テーブル名コメント */
            $table->comment('アカウント情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};
