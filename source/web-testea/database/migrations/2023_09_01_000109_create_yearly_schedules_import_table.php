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
        Schema::create('yearly_schedules_import', function (Blueprint $table) {
            /* カラム */
            $table->increments('yearly_schedules_import_id')->comment('年間予定取込情報ID');
            $table->string('school_year', 4)->comment('年度');
            $table->string('campus_cd', 2)->comment('校舎コード');
            $table->unsignedSmallInteger('import_state')->default(0)->comment('取込状態（0:取込未、1:取込済）');
            $table->timestamp('import_date')->nullable()->comment('取込日時');
            $table->timestamps();
            $table->softDeletes();

            /* インデックス */
            $table->unique(['school_year','campus_cd'],'yearly_schedules_import_UNIQUE');

            /* テーブル名コメント */
            $table->comment('教室年間予定取込情報');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yearly_schedules_import');
    }
};
