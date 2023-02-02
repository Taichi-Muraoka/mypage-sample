<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_apply', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('leave_apply_id')->autoIncrement()->comment('退会申請ID');
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->text('leave_reason')->comment('退会理由');
            $table->date('apply_time')->default('1000-01-01')->comment('申請日');
            $table->unsignedTinyInteger('leave_state')->comment('退会状態（0:未対応、1:受付、2:退会処理中、3:退会済）');
            $table->text('comment')->nullable()->comment('事務局コメント');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->index('sid','leave_apply_sid_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_apply');
    }
}
