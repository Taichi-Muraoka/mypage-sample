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
        Schema::create('admin_users', function (Blueprint $table) {
            /* カラム */
            $table->increments('adm_id')->comment('管理者ID');
            $table->string('name', 50)->comment('名前');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('管理者アカウント情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_users');
    }
};
