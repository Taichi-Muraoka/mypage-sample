<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \SplFileObject;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * 本システムのテストデータを投入
 */
class MypageDevTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 実行する場合
        //composer dump-autoload
        //php artisan db:seed --class=MypageDummyDataSeeder

        // CSVから読み込みと登録

        $this->loadCsv('database/csv/devtest/accounts.csv', 'accounts');
        $this->loadCsv('database/csv/devtest/admin_users.csv', 'admin_users');
        $this->loadCsv('database/csv/devtest/mst_codes.csv', 'mst_codes');
        $this->loadCsv('database/csv/devtest/mst_campuses.csv', 'mst_campuses');
        $this->loadCsv('database/csv/devtest/mst_booths.csv', 'mst_booths');
        $this->loadCsv('database/csv/devtest/mst_courses.csv', 'mst_courses');
        $this->loadCsv('database/csv/devtest/mst_grades.csv', 'mst_grades');
        $this->loadCsv('database/csv/devtest/mst_tutor_grades.csv', 'mst_tutor_grades');
        $this->loadCsv('database/csv/devtest/mst_subjects.csv', 'mst_subjects');
        $this->loadCsv('database/csv/devtest/mst_grade_subjects.csv', 'mst_grade_subjects');
        $this->loadCsv('database/csv/devtest/mst_prefectures.csv', 'mst_prefectures');
        $this->loadCsv('database/csv/devtest/mst_schools.csv', 'mst_schools');
        $this->loadCsv('database/csv/devtest/mst_systems.csv', 'mst_systems');
        $this->loadCsv('database/csv/devtest/mst_texts.csv', 'mst_texts');
        $this->loadCsv('database/csv/devtest/mst_timetables.csv', 'mst_timetables');
        $this->loadCsv('database/csv/devtest/mst_unit_categories.csv', 'mst_unit_categories');
        $this->loadCsv('database/csv/devtest/mst_units.csv', 'mst_units');
        $this->loadCsv('database/csv/devtest/students.csv', 'students');
        $this->loadCsv('database/csv/devtest/student_campuses.csv', 'student_campuses');
        $this->loadCsv('database/csv/devtest/tutors.csv', 'tutors');
        $this->loadCsv('database/csv/devtest/tutor_campuses.csv', 'tutor_campuses');
        $this->loadCsv('database/csv/devtest/tutor_subjects.csv', 'tutor_subjects');
        $this->loadCsv('database/csv/devtest/regular_classes.csv', 'regular_classes');
        $this->loadCsv('database/csv/devtest/regular_class_members.csv', 'regular_class_members');
        $this->loadCsv('database/csv/devtest/yearly_schedules.csv', 'yearly_schedules');
        //$this->loadCsv('database/csv/devtest/schedules.csv', 'schedules');
        //$this->loadCsv('database/csv/devtest/class_members.csv', 'class_members');
        $this->loadCsv('database/csv/devtest/notice_group.csv', 'notice_groups');
        $this->loadCsv('database/csv/devtest/season_mng.csv', 'season_mng');
        $this->loadCsv('database/csv/devtest/season_student_requests.csv', 'season_student_requests');
        $this->loadCsv('database/csv/devtest/invoice_import.csv', 'invoice_import');
        $this->loadCsv('database/csv/devtest/salary_mng.csv', 'salary_mng');
        $this->loadCsv('database/csv/devtest/salary_import.csv', 'salary_import');
        $this->loadCsv('database/csv/devtest/yearly_schedules_import.csv', 'yearly_schedules_import');

    }

    /**
     * CSV読み込み
     *
     * @param $path CSVのパス
     * @param $table 登録先テーブル
     */
    private function loadCsv($path, $table)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // 多分、Windowsのためか、日本語が入ったUTF-8のCSVを読み込めず・・
            // 以下のコードを指定。Linuxでは不要だと思う。（後ほど試す）
            setLocale(LC_ALL, 'English_United States.1252');
        }

        // CSV読み込み
        $file = new SplFileObject($path);
        $file->setFlags(
            \SplFileObject::READ_CSV |
                \SplFileObject::READ_AHEAD |
                \SplFileObject::SKIP_EMPTY |
                \SplFileObject::DROP_NEW_LINE
        );

        $list = [];
        $headers = [];
        // 現在日時を取得
        $now = Carbon::now();

        foreach ($file as $i => $line) {
            if ($i === 0) {
                $headers = $line;
                continue;
            }

            //var_dump($headers);
            //var_dump($line);

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // 日時をセットする
            $values['created_at'] = $now;
            $values['updated_at'] = $now;
            $values['deleted_at'] = null;

            // 調整
            foreach ($values as $key => $val) {

                // passwordはハッシュ化する
                if ($key === 'password') {
                    $values[$key] = Hash::make($val);
                }

                // 更新日は現在日付
                if ($key === 'updtime') {
                    $values[$key] = $now;
                }

                // 空白はnullにしてみる
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // リストに保持しておく
            $list[] = $values;
        }

        // 一旦テーブルをクリア
        DB::table($table)->truncate();
        // まとめて登録
        DB::table($table)->insert($list);
    }
}
