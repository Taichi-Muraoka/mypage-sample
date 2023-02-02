<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryImportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_import', function (Blueprint $table) {
            /*カラム*/
            $table->date('salary_date')->comment('給与月');
            $table->unsignedTinyInteger('import_state')->comment('取込状態（0:取込未、1:取込済）');
            $table->timestamp('import_date')->nullable()->comment('取込日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary('salary_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_import');
    }
}
