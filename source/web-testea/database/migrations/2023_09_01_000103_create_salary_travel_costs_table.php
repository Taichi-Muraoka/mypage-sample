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
        Schema::create('salary_travel_costs', function (Blueprint $table) {
            /* カラム */
            $table->increments('salary_travel_cost_id')->comment('給与算出交通費ID');
            $table->date('salary_date')->comment('給与年月');
            $table->unsignedInteger('tutor_id')->comment('講師ID');
            $table->unsignedSmallInteger('seq')->comment('連番');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->decimal('unit_price', 6, 0)->default(0)->comment('単価');
            $table->unsignedSmallInteger('times')->default(0)->comment('回数');
            $table->decimal('amount', 6, 0)->default(0)->comment('金額');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['salary_date','tutor_id','seq'],'salary_travel_costs_UNIQUE');

            /* テーブル名コメント */
            $table->comment('給与算出交通費情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_travel_costs');
    }
};
