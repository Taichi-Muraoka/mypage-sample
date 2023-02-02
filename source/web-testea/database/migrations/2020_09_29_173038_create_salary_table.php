<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary', function (Blueprint $table) {
            /*カラム*/
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->date('salary_date')->comment('給与月');
            $table->text('tax_table')->comment('税額表');
            $table->decimal('dependents', 2, 0)->comment('扶養人数');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['tid','salary_date']);

            /*外部キー*/
            // $table->foreign('tid')->references('tid')->on('ext_teacher');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary');
    }
}
