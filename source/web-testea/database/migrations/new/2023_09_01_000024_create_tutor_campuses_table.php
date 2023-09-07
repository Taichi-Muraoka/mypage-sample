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
        Schema::create('tutor_campuses', function (Blueprint $table) {
            /* カラム */
            $table->increments('tutor_campus_id')->comment('講師所属ID');
            $table->unsignedInteger('tutor_id')->nullable()->comment('講師ID');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->decimal('travel_cost', 4, 0)->default(0)->comment('交通費');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['tutor_id','campus_cd'],'tutor_campuses_UNIQUE');

            /* テーブル名コメント */
            $table->comment('講師所属情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tutor_campuses');
    }
};
