<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_detail', function (Blueprint $table) {
            /*カラム*/
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->date('salary_date')->comment('給与月');
            $table->decimal('salary_seq', 8, 0)->comment('給与情報通番');
            $table->unsignedSmallInteger('order_code')->comment('表示順');
            $table->unsignedSmallInteger('salary_group')->comment('給与表示グループ（1:支給、2:控除、3:その他、4:合計）');
            $table->text('item_name')->comment('項目名');
            $table->decimal('amount', 8, 0)->comment('金額');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['tid','salary_date','salary_seq']);

            /*外部キー*/
            // $table->foreign(['tid','salary_date'],'salary_detail_tid')->references(['tid','salary_date'])->on('salary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_detail');
    }
}
