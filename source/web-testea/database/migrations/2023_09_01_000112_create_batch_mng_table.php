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
        Schema::create('batch_mng', function (Blueprint $table) {
            /* カラム */
            $table->bigIncrements('batch_id')->comment('バッチID');
            $table->unsignedSmallInteger('batch_type')->comment('バッチ種別');
            $table->timestamp('start_time')->useCurrent()->comment('処理開始日時');
            $table->timestamp('end_time')->nullable()->comment('処理終了日時');
            $table->unsignedSmallInteger('batch_state')->comment('バッチ状態(99:実行中,0:正常終了,1:異常終了)');
            $table->unsignedInteger('adm_id')->nullable()->comment('管理者ID（実行者）');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('バッチ実行管理');
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
};
