<?php

namespace App\Http\Controllers\Traits;

use TCPDF;
use Illuminate\Support\Facades\Lang;
use App\Models\ExtRirekisho;
use App\Models\Salary;
use App\Models\SalaryDetail;
use App\Consts\AppConst;

/**
 * 給与 - 機能共通処理
 */
trait FuncSalaryTrait
{

    /**
     * 詳細データの取得
     *
     * @param int $tid 教師ID
     * @return array
     */
    private function getSalaryDetail($tid, $date)
    {

        // dateの形式のバリデーションと変換
        $idDate = $this->fmYmToDate($date);

        // 給与明細を取得する
        $query = Salary::query();
        $salary = $query
            ->select(
                'salary_date',
                'tax_table',
                'dependents',
                'ext_rirekisho.name AS teacher_name'
            )
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'salary.tid');
            })
            ->where('salary.tid', '=', $tid)
            ->where('salary_date', '=', $idDate)
            ->firstOrFail();

        $salary['prev_month'] = $salary['salary_date']->modify('-1 months');

        // 給与情報明細 支給を取得する
        $query = SalaryDetail::query();
        $salary_detail_1 = $query
            ->select(
                'item_name',
                'amount',
            )
            ->where('tid', '=', $tid)
            ->where('salary_date', '=', $idDate)
            ->whereNotIn('amount', [0])
            ->where('salary_group', '=', AppConst::SALARY_GROUP_1)
            ->orderBy('order_code', 'asc')
            ->get();

        // 給与情報明細 控除を取得する
        $query = SalaryDetail::query();
        $salary_detail_2 = $query
            ->select(
                'item_name',
                'amount',
            )
            ->where('tid', '=', $tid)
            ->where('salary_date', '=', $idDate)
            ->whereNotIn('amount', [0])
            ->where('salary_group', '=', AppConst::SALARY_GROUP_2)
            ->orderBy('order_code', 'asc')
            ->get();

        // 給与情報明細 その他を取得する
        $query = SalaryDetail::query();
        $salary_detail_3 = $query
            ->select(
                'item_name',
                'amount',
            )
            ->where('tid', '=', $tid)
            ->where('salary_date', '=', $idDate)
            ->whereNotIn('amount', [0])
            ->where('salary_group', '=', AppConst::SALARY_GROUP_3)
            ->orderBy('order_code', 'asc')
            ->get();

        // 給与情報明細 合計を取得する
        $query = SalaryDetail::query();
        $salary_detail_4 = $query
            ->select(
                'item_name',
                'amount',
            )
            ->where('tid', '=', $tid)
            ->where('salary_date', '=', $idDate)
            ->where('salary_group', '=', AppConst::SALARY_GROUP_4)
            ->orderBy('order_code', 'asc')
            ->get();

        $pdfData = [
            'salary' => $salary,
            'salary_detail_1' => $salary_detail_1,
            'salary_detail_2' => $salary_detail_2,
            'salary_detail_3' => $salary_detail_3,
            'salary_detail_4' => $salary_detail_4,
        ];

        return $pdfData;
    }

    /**
     * PDF出力処理 給与
     *
     * @param $id ID
     */
    private function outputPdfSalary($data)
    {

        // PDFインスタンスを取得(P:縦向き)
        $pdf = new TCPDF("P", "mm", "A4", true, "UTF-8");

        // header/footerなし
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ページを追加
        $pdf->addPage();

        // フォントの指定(事前にフォントをインストールする必要がある)
        $pdf->SetFont('ipaexg', '', 14);

        $pdf->writeHTML(view("pdf.tutor.salary", $data)->render());

        $date_str = $data['salary']->salary_date->format('Y年m月');
        $teacher_name = $data['salary']->teacher_name;

        // ファイル名
        $filename = Lang::get(
            'message.pdf.salary.name',
            [
                'date' => $date_str,
                'name' => $teacher_name
            ]
        );

        // ファイルダウンロードヘッダーの指定
        $this->fileDownloadHeader($filename);

        // PDF出力(S=PDFの内容を文字列で出力)
        // Sの場合、$filenameはOutputの中では使ってなかった。(buffer返しているだけ)
        // サーバーにファイルを保存していないので良かった。
        $pdf_output = $pdf->Output($filename, "S");
        print $pdf_output;
    }
}
