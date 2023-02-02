<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtRirekishoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_rirekisho', function (Blueprint $table) {
            /*カラム*/
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->string('name', 50)->comment('名前');
            $table->string('mailaddress1', 100)->comment('メールアドレス1');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary('tid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_rirekisho');
    }
}
