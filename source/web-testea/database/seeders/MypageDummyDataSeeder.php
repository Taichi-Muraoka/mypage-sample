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
class MypageDummyDataSeeder extends Seeder
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

        $this->loadCsv('database/csv/account.csv', 'account');
        $this->loadCsv('database/csv/ext_room.csv', 'ext_room');
        $this->loadCsv('database/csv/ext_student_kihon.csv', 'ext_student_kihon');
        $this->loadCsv('database/csv/ext_rirekisho.csv', 'ext_rirekisho');
        $this->loadCsv('database/csv/office.csv', 'office');

        $this->loadCsv('database/csv/ext_generic_master.csv', 'ext_generic_master');
        $this->loadCsv('database/csv/code_master.csv', 'code_master');
        $this->loadCsv('database/csv/notice_group.csv', 'notice_group');

        $this->loadCsv('database/csv/ext_regular.csv', 'ext_regular');
        $this->loadCsv('database/csv/ext_regular_detail.csv', 'ext_regular_detail');
        $this->loadCsv('database/csv/ext_home_teacher_std.csv', 'ext_home_teacher_std');
        $this->loadCsv('database/csv/ext_home_teacher_std_detail.csv', 'ext_home_teacher_std_detail');
        $this->loadCsv('database/csv/ext_extra_individual.csv', 'ext_extra_individual');
        $this->loadCsv('database/csv/ext_extra_ind_detail.csv', 'ext_extra_ind_detail');
        $this->loadCsv('database/csv/tutor_relate.csv', 'tutor_relate');

        $this->loadCsv('database/csv/ext_schedule.csv', 'ext_schedule');
        $this->loadCsv('database/csv/ext_trial_master.csv', 'ext_trial_master');

        $this->loadCsv('database/csv/invoice_import.csv', 'invoice_import');
        $this->loadCsv('database/csv/invoice.csv', 'invoice');
        $this->loadCsv('database/csv/invoice_detail.csv', 'invoice_detail');
        $this->loadCsv('database/csv/salary_import.csv', 'salary_import');
        $this->loadCsv('database/csv/salary.csv', 'salary');
        $this->loadCsv('database/csv/salary_detail.csv', 'salary_detail');

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
