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
        Schema::create('salaries', function (Blueprint $table) {
            /* カラム */
            $table->increments('salary_id')->comment('給与ID');
            $table->unsignedInteger('tutor_id')->comment('講師ID');
            $table->date('salary_date')->comment('給与年月');
            $table->decimal('total_amount', 8, 0)->comment('支払金額');
            $table->text('memo')->comment('備考');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['tutor_id','salary_date'],'salaries_UNIQUE');

            /* テーブル名コメント */
            $table->comment('給与情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salaries');
    }
};
