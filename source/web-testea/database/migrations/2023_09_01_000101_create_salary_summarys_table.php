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
        Schema::create('salary_summarys', function (Blueprint $table) {
            /* カラム */
            $table->increments('salary_summary_id')->comment('給与算出ID');
            $table->date('salary_date')->comment('給与年月');
            $table->unsignedInteger('tutor_id')->comment('講師ID');
            $table->unsignedSmallInteger('summary_kind')->comment('給与集計種別');
            $table->decimal('hour_payment', 8, 0)->nullable()->comment('単価');
            $table->decimal('hour', 5, 2)->nullable()->comment('時間（h）');
            $table->decimal('amount', 8, 0)->comment('金額');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['salary_date','tutor_id','summary_kind'],'salary_temp_details_UNIQUE');

            /* テーブル名コメント */
            $table->comment('給与算出情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_summarys');
    }
};
