<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtTrialMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_trial_master', function (Blueprint $table) {
            /*カラム*/
            $table->integer('tmid')->comment('模試ID');
            $table->string('name', 60)->comment('模試名');
            $table->string('symbol', 4)->comment('スケジュール表示用シンボル');
            $table->string('cls_cd', 2)->comment('学年');
            $table->decimal('price', 6, 0)->comment('受験料');
            $table->date('trial_date')->comment('受験日');
            $table->time('start_time', 0)->comment('開始時刻');
            $table->time('end_time', 0)->comment('終了時刻');
            $table->decimal('disp_flg', 1, 0)->comment('表示対象フラグ（0:表示対象外，1:表示対象）');
            $table->timestamp('updtime', 0)->useCurrent()->comment('業務支援システム更新日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary('tmid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ext_trial_master');
    }
}
