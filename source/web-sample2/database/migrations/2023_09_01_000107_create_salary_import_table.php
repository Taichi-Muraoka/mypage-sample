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
        Schema::create('salary_import', function (Blueprint $table) {
            /* カラム */
            $table->date('salary_date')->comment('給与年月');
            $table->date('payment_date')->nullable()->comment('支給日');
            $table->unsignedSmallInteger('import_state')->default(0)->comment('取込状態（0:取込未、1:取込済）');
            $table->timestamp('import_date')->nullable()->comment('取込日時');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->primary('salary_date');

            /* テーブル名コメント */
            $table->comment('給与取込情報');
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
};
