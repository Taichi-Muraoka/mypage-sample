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
        Schema::create('salary_details', function (Blueprint $table) {
            /* カラム */
            $table->increments('salary_detail_id')->comment('給与明細ID');
            $table->unsignedInteger('salary_id')->comment('給与ID');
            $table->unsignedSmallInteger('salary_seq')->comment('明細番号');
            $table->unsignedSmallInteger('salary_group')->comment('明細表示グループ');
            $table->string('item_name', 50)->comment('費目名');
            $table->decimal('hour_payment', 8, 0)->nullable()->comment('単価');
            $table->decimal('hour', 5, 2)->nullable()->comment('時間（h）');
            $table->decimal('amount', 8, 0)->comment('金額');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['salary_id','salary_seq'],'salary_details_UNIQUE');

            /* テーブル名コメント */
            $table->comment('給与明細情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_details');
    }
};
