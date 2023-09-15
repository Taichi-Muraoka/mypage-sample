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
        Schema::create('mst_campuses', function (Blueprint $table) {
            /* カラム */
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->string('name', 50)->comment('名称');
            $table->string('short_name', 10)->comment('略称');
            $table->string('email_campus', 100)->comment('校舎メールアドレス');
            $table->string('tel_campus', 20)->comment('校舎電話番号');
            $table->unsignedSmallInteger('disp_order')->comment('表示順');
            $table->unsignedSmallInteger('is_hidden')->default(0)->comment('非表示フラグ（0:表示、1:非表示）');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('campus_cd');

            /* テーブル名コメント */
            $table->comment('校舎マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_campuses');
    }
};
