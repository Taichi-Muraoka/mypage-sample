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
        Schema::create('mst_schools', function (Blueprint $table) {
            /* カラム */
            $table->string('school_cd', 13)->comment('学校コード');
            $table->string('school_kind', 2)->comment('学校種');
            $table->unsignedSmallInteger('school_kind_cd')->comment('学校種コード');
            $table->string('pref_cd', 2)->comment('都道府県番号');
            $table->unsignedSmallInteger('establish_kind')->comment('設置区分');
            $table->unsignedSmallInteger('branch_kind')->comment('本分校区分');
            $table->string('name', 50)->comment('学校名');
            $table->string('address', 100)->comment('学校所在地');
            $table->string('post_code', 7)->comment('郵便番号');
            $table->date('setting_date')->nullable()->comment('設定年月日');
            $table->date('abolition_date')->nullable()->comment('廃止年月日');
            $table->string('old_shool_cd', 6)->nullable()->comment('旧学校調査番号');
            $table->string('change_flg', 100)->nullable()->comment('移行後学校コード');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('school_cd');

            /* テーブル名コメント */
            $table->comment('学校マスタ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_schools');
    }
};
