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
        Schema::create('training_browses', function (Blueprint $table) {
            /* カラム */
            $table->unsignedInteger('trn_id')->comment('研修ID');
            $table->unsignedInteger('tutor_id')->comment('講師ID');
            $table->timestamp('browse_time')->nullable()->comment('閲覧日時');
            $table->timestamps();
            $table->softDeletes();

            /*インデックス*/
            $table->primary(['trn_id','tutor_id']);
            $table->index('tutor_id','training_browse_tid_idx');

            /* テーブル名コメント */
            $table->comment('研修閲覧');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_browses');
    }
};
