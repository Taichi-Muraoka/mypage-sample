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
        Schema::create('invoices', function (Blueprint $table) {
            /* カラム */
            $table->increments('invoice_id')->comment('請求書ID');
            $table->unsignedInteger('student_id')->comment('生徒ID');
            $table->date('invoice_date')->comment('請求書年月');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->unsignedSmallInteger('pay_type')->comment('支払方法（1:口座引落、2:振込）');
            $table->decimal('total_amount', 8, 0)->comment('請求金額');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['student_id','invoice_date'],'invoices_UNIQUE');

            /* テーブル名コメント */
            $table->comment('請求情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
