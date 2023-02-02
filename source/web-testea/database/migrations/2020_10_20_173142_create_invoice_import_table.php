<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceImportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_import', function (Blueprint $table) {
            /*カラム*/
            $table->date('invoice_date')->comment('請求月');
            $table->unsignedTinyInteger('import_state')->comment('取込状態（0:取込未、1:取込済）');
            $table->timestamp('import_date')->nullable()->comment('取込日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_import');
    }
}
