<?php

namespace App\Http\Controllers\Traits;

use TCPDF;
use Illuminate\Support\Facades\Lang;
use App\Models\Salary;
use App\Models\SalaryDetail;
use App\Consts\AppConst;
use App\Models\SalaryImport;
use App\Models\Tutor;

/**
 * 給与 - 機能共通処理
 */
trait FuncSalaryTrait
{

    /**
     * 詳細データの取得
     *
     * @param int $tid 講師ID
     * @return array
     */
    private function getSalaryDetail($tid, $date)
    {
        // dateの形式のバリデーションと変換
        $idDate = $this->fmYmToDate($date);

        //-------------------------
        // 給与取込情報の取得
        //-------------------------
        // MEMO:joinだとbladeファイルで->format()が効かないため単独で取得
        $query = SalaryImport::query();
        $salary_import = $query
            ->select(
                'payment_date',
            )
            ->where('salary_import.salary_date', '=', $idDate)
            ->firstOrFail();

        //-------------------------
        // 給与情報の取得
        //-------------------------
        $query = Salary::query();
        $salary = $query
            ->select(
                'salaries.salary_id',
                'salaries.salary_date',
                'salaries.total_amount',
                'salaries.memo',
                'tutors.name as tutor_name'
            )
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('tutors.tutor_id', '=', 'salaries.tutor_id');
            })
            ->where('salaries.tutor_id', '=', $tid)
            ->where('salaries.salary_date', '=', $idDate)
            ->firstOrFail();

        // 備考の改行処理 読点を改行コードに置換
        $salary['memo'] = str_replace("、", "\n", $salary['memo']);

        //-------------------------
        // 給与明細情報 源泉計算対象の取得
        //-------------------------
        $query = SalaryDetail::query();
        $salary_detail_1 = $query
            ->select(
                'item_name',
                'hour_payment',
                'hour',
                'amount',
            )
            ->where('salary_id', '=', $salary['salary_id'])
            ->whereNotIn('amount', [0])
            ->where('salary_group', '=', AppConst::SALARY_GROUP_1)
            ->orderBy('salary_seq', 'asc')
            ->get();

        //-------------------------
        // 給与明細情報 源泉計算対象外の取得
        //-------------------------
        $query = SalaryDetail::query();
        $salary_detail_2 = $query
            ->select(
                'item_name',
                'amount',
            )
            ->where('salary_id', '=', $salary['salary_id'])
            ->whereNotIn('amount', [0])
            ->where('salary_group', '=', AppConst::SALARY_GROUP_2)
            ->orderBy('salary_seq', 'asc')
            ->get();

        // 小計の計算
        $salary_detail_2_subtotal = 0;
        foreach ($salary_detail_2 as $detail) {
            $salary_detail_2_subtotal += $detail['amount'];
        }

        //-------------------------
        // 給与明細情報 控除の取得
        //-------------------------
        $query = SalaryDetail::query();
        $salary_detail_3 = $query
            ->select(
                'item_name',
                'amount',
            )
            ->where('salary_id', '=', $salary['salary_id'])
            ->whereNotIn('amount', [0])
            ->where('salary_group', '=', AppConst::SALARY_GROUP_3)
            ->orderBy('salary_seq', 'asc')
            ->get();

        // 年末調整の金額の符号反転
        // 小計の計算
        $salary_detail_3_subtotal = 0;
        foreach ($salary_detail_3 as $detail) {
            if ($detail['item_name'] == '年末調整') {
                $detail['amount'] = $detail['amount'] * -1;
            }
            $salary_detail_3_subtotal += $detail['amount'];
        }

        $pdfData = [
            'salary_import' => $salary_import,
            'salary' => $salary,
            'salary_detail_1' => $salary_detail_1,
            'salary_detail_2' => $salary_detail_2,
            'salary_detail_2_subtotal' => $salary_detail_2_subtotal,
            'salary_detail_3' => $salary_detail_3,
            'salary_detail_3_subtotal' => $salary_detail_3_subtotal,
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
        $pdf->SetFont('ipaexg', '', 13);

        $pdf->writeHTML(view("pdf.tutor.salary", $data)->render());

        $date_str = $data['salary']->salary_date->format('Y年m月');
        $tutor_name = $data['salary']->tutor_name;

        // ファイル名
        $filename = Lang::get(
            'message.pdf.salary.name',
            [
                'date' => $date_str,
                'name' => $tutor_name
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
