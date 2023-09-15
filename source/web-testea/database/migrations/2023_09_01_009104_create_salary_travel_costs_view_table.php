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
CREATE VIEW salary_travel_costs_view AS
SELECT
MIN(CASE WHEN seq = 1 THEN salary_travel_cost_id ELSE null END) AS salary_travel_costs_view_id,
salary_date,
tutor_id,
MAX(CASE WHEN seq = 1 THEN unit_price ELSE 0 END) AS unit_price1,
MAX(CASE WHEN seq = 1 THEN times ELSE 0 END) AS times1,
MAX(CASE WHEN seq = 1 THEN amount ELSE 0 END) AS amount1,
MAX(CASE WHEN seq = 2 THEN unit_price ELSE 0 END) AS unit_price2,
MAX(CASE WHEN seq = 2 THEN times ELSE 0 END) AS times2,
MAX(CASE WHEN seq = 2 THEN amount ELSE 0 END) AS amount2,
MAX(CASE WHEN seq = 3 THEN unit_price ELSE 0 END) AS unit_price3,
MAX(CASE WHEN seq = 3 THEN times ELSE 0 END) AS times3,
MAX(CASE WHEN seq = 3 THEN amount ELSE 0 END) AS amount3
FROM salary_travel_costs
GROUP BY salary_date,tutor_id
EOT;

        DB::statement('DROP VIEW IF EXISTS salary_travel_costs_view');
        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS salary_travel_costs_view');
    }
};
