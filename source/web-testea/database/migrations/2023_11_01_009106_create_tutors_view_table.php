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
CREATE VIEW tutors_view AS
SELECT
*,
CASE WHEN leave_date IS NULL
  THEN PERIOD_DIFF(DATE_FORMAT(curdate(), '%Y%m'), DATE_FORMAT(enter_date, '%Y%m')) + 1
  ELSE  PERIOD_DIFF(DATE_FORMAT(leave_date, '%Y%m'), DATE_FORMAT(enter_date, '%Y%m')) + 1
END  AS enter_term
FROM tutors
EOT;

        DB::statement('DROP VIEW IF EXISTS tutors_view');
        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS tutors_view');
    }
};
