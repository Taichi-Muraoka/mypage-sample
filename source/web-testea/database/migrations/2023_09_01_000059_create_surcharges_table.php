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
        Schema::create('surcharges', function (Blueprint $table) {
            /* カラム */
            $table->increments('surcharge_id')->comment('追加請求ID');
            $table->unsignedInteger('tutor_id')->comment('講師ID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->date('apply_date')->comment('申請日');
            $table->unsignedInteger('surcharge_kind')->comment('請求種別');
            $table->date('working_date')->nullable()->comment('実施日');
            $table->time('start_time')->nullable()->comment('開始時刻');
            $table->unsignedInteger('minutes')->nullable()->comment('時間（分）');
            $table->decimal('tuition', 8, 0)->nullable()->comment('金額');
            $table->text('comment')->nullable()->comment('内容');
            $table->unsignedSmallInteger('approval_status')->default(1)->comment('承認状態（1:承認待ち、2:承認、3:差戻し）');
            $table->date('payment_date')->nullable()->comment('支払年月');
            $table->unsignedSmallInteger('payment_status')->default(0)->comment('支払状況（0:未処理、1:支払済）');
            $table->text('admin_comment')->nullable()->comment('管理者コメント');
            $table->timestamps();
            $table->softDeletes();

            /* テーブル名コメント */
            $table->comment('追加請求情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('surcharges');
    }
};
