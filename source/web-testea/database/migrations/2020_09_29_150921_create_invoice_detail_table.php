<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_detail', function (Blueprint $table) {
            /*カラム*/
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->date('invoice_date')->comment('請求月');
            $table->unsignedSmallInteger('lesson_type')->comment('授業種別（1:個別教室、2:家庭教師）');
            $table->decimal('invoice_seq', 8, 0)->comment('請求情報通番');
            $table->unsignedSmallInteger('order_code')->comment('表示順');
            $table->text('cost_name')->comment('費用内容');
            $table->decimal('cost', 8, 0)->nullable()->comment('費用');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['sid','invoice_date','lesson_type','invoice_seq']);

            /*外部キー*/
            // $table->foreign(['sid','invoice_date','lesson_type'],'invoice_detail_sid_invoice_date')->references(['sid','invoice_date','lesson_type'])->on('invoice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_detail');
    }
}
