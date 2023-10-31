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
        $sql = <<<EOT
CREATE VIEW students_view AS
SELECT
*,
CASE WHEN stu_status = 5
  THEN past_enter_term
  ELSE past_enter_term + PERIOD_DIFF(DATE_FORMAT(curdate(), '%Y%m'), DATE_FORMAT(enter_date, '%Y%m')) + 1
END  as enter_term
FROM students
EOT;

        DB::statement('DROP VIEW IF EXISTS students_view');
        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS students_view');
    }
};
