<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAverageToGradesDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grades_detail', function (Blueprint $table) {
            // カラム定義変更
            $table->decimal('average', 3, 0)->comment('平均点')->nullable()->default(NULL)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grades_detail', function (Blueprint $table) {
            $table->decimal('average', 3, 0)->comment('平均点')->change();
        });
    }
}
