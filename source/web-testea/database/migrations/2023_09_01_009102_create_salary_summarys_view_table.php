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
CREATE VIEW salary_summarys_view AS
SELECT
MIN(CASE WHEN summary_kind = 1 THEN salary_summary_id ELSE null END) AS salary_summarys_view_id,
salary_date AS salary_date,
tutor_id AS tutor_id,
MAX(CASE WHEN summary_kind = 1 THEN hour_payment ELSE 0 END) AS hourly_base_wage,
MAX(CASE WHEN summary_kind = 1 THEN hour ELSE 0 END) AS hour_personal,
MAX(CASE WHEN summary_kind = 2 THEN hour ELSE 0 END) AS hour_two,
MAX(CASE WHEN summary_kind = 3 THEN hour ELSE 0 END) AS hour_three,
MAX(CASE WHEN summary_kind = 4 THEN hour ELSE 0 END) AS hour_group,
MAX(CASE WHEN summary_kind = 5 THEN hour ELSE 0 END) AS hour_home,
MAX(CASE WHEN summary_kind = 6 THEN hour ELSE 0 END) AS hour_practice,
MAX(CASE WHEN summary_kind = 7 THEN hour ELSE 0 END) AS hour_high,
MAX(CASE WHEN summary_kind = 8 THEN hour_payment ELSE 0 END) AS hourly_work_wage,
MAX(CASE WHEN summary_kind = 8 THEN hour ELSE 0 END) AS hour_work,
MAX(CASE WHEN summary_kind = 9 THEN amount ELSE 0 END) AS cost,
MAX(CASE WHEN summary_kind = 10 THEN amount ELSE 0 END) AS untaxed_cost
FROM salary_summarys
GROUP BY salary_date,tutor_id
EOT;

        DB::statement('DROP VIEW IF EXISTS salary_summarys_view');
        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS salary_summarys_view');
    }
};
