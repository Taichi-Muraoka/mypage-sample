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
        Schema::create('training_contents', function (Blueprint $table) {
            /* カラム */
            $table->increments('trn_id')->comment('研修ID');
            $table->unsignedSmallInteger('trn_type')->comment('研修形式（1:資料、2:動画）');
            $table->text('text')->comment('研修内容');
            $table->text('url')->comment('研修資料URL（ファイルならはサーバ内のURL、動画なら外部のURL）');
            $table->date('regist_time')->default('1000-01-01')->comment('登録日');
            $table->date('release_date')->comment('公開日');
            $table->date('limit_date')->nullable()->comment('研修期限日');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('研修資料');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_contents');
    }
};
