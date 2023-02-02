<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            /*カラム*/
            $table->decimal('sid', 8, 0)->comment('生徒No.');
            $table->date('invoice_date')->comment('請求月');
            $table->unsignedSmallInteger('lesson_type')->comment('授業種別（1:個別教室、2:家庭教師）');
            $table->smallInteger('pay_type')->comment('請求方法');
            $table->text('agreement')->nullable()->comment('契約内容');
            $table->date('issue_date')->comment('発行日');
            $table->date('bill_date')->nullable()->comment('引落日');
            $table->text('note')->nullable()->comment('備考');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['sid','invoice_date','lesson_type']);

            /*外部キー*/
            // $table->foreign('sid')->references('sid')->on('ext_student');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice');
    }
}
