<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingBrowseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_browse', function (Blueprint $table) {
            /*カラム*/
            $table->bigInteger('trn_id')->comment('研修ID');
            $table->decimal('tid', 6, 0)->comment('教師No.');
            $table->timestamp('browse_time', 0)->nullable()->comment('閲覧日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['trn_id','tid']);
            $table->index('tid','training_browse_tid_idx');

            /*外部キー*/
            // $table->foreign('tid','training_browse_tid')->references('tid')->on('ext_teacher');
            // $table->foreign('trn_id','training_browse_trn_id')->references('trn_id')->on('training_contents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_browse');
    }
}
