<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchMngTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_mng', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('batch_id')->autoIncrement()->comment('バッチID');
            $table->smallInteger('batch_type')->comment('バッチ種別（1:年次生徒情報,2:年次スケジュール情報,3:保存期間超過データ削除,4:データベースバックアップ,5:年次初期データ作成,6:会員情報データ移行）');
            $table->timestamp('start_time', 0)->useCurrent()->comment('処理開始日時');
            $table->timestamp('end_time', 0)->nullable()->comment('処理終了日時');
            $table->unsignedTinyInteger('batch_state')->comment('バッチ状態(99:実行中,0:正常終了,1:異常終了)');
            $table->bigInteger('adm_id')->nullable()->comment('事務局ID（実行者）');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_mng');
    }
}
