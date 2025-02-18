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
        Schema::create('invoice_details', function (Blueprint $table) {
            /* カラム */
            $table->increments('invoice_detail_id')->comment('請求書明細ID');
            $table->unsignedInteger('invoice_id')->comment('請求書ID');
            $table->unsignedSmallInteger('invoice_seq')->comment('明細連番');
            $table->string('description', 50)->comment('摘要');
            $table->decimal('unit_price', 8, 0)->nullable()->comment('単価');
            $table->unsignedSmallInteger('times')->nullable()->comment('コマ数');
            $table->decimal('amount', 8, 0)->comment('金額');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['invoice_id','invoice_seq'],'invoice_details_UNIQUE');

            /* テーブル名コメント */
            $table->comment('請求明細情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_details');
    }
};
