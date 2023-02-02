<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_contents', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('trn_id')->autoIncrement()->comment('研修ID');
            $table->smallInteger('trn_type')->comment('研修形式（1:資料、2:動画）');
            $table->text('text')->comment('研修内容');
            $table->text('url')->comment('研修資料URL（ファイルならはサーバ内のURL、動画なら外部のURL）');
            $table->date('regist_time')->default('1000-01-01')->comment('登録日');
            $table->date('release_date')->comment('公開日');
            $table->date('limit_date')->nullable()->comment('研修期限日');
            $table->timestamps();
            $table->softDeletes();

            /*外部キー*/
            // $table->foreign('trn_id','training_browse_trn_id')->references('trn_id')->on('training_browse');
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
}
