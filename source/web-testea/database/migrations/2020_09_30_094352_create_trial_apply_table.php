<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrialApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trial_apply', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('trial_apply_id')->autoIncrement()->comment('模試申込ID');
            $table->integer('tmid')->comment('模試ID');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->tinyInteger('apply_state')->default(0)->comment('申込状態（0:未対応、1:受付済）');
            $table->date('apply_time')->default('1000-01-01')->comment('申込日');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('sid','trial_apply_sid_idx');
            $table->index('tmid','trial_apply_tmid_idx');

            /*外部キー*/
            // $table->foreign('sid','trial_apply_sid')->references('sid')->on('ext_student');
            // $table->foreign('tmid','trial_apply_tmid')->references('tmid')->on('ext_trial_master');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trial_apply');
    }
}
