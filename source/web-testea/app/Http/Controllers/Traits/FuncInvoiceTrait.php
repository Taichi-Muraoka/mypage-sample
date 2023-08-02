<?php

namespace App\Http\Controllers\Traits;

use TCPDF;
use Illuminate\Support\Facades\Lang;
use App\Models\InvoiceDetail;
use App\Models\ExtGenericMaster;
use App\Models\ExtStudentKihon;
use App\Consts\AppConst;
use App\Models\Invoice;

/**
 * 請求書 - 機能共通処理
 */
trait FuncInvoiceTrait
{

    /**
     * 請求情報の取得
     *
     * @param string date 請求年月（yyyymm）
     * @param string sid 生徒No
     * @return array 
     */
    private function getInvoiceDetail($date, $sid)
    {

        // MEMO: 個別・家庭教師両方の場合、重複する明細はどちらか１方にのみ金額が設定される。
        //       金額0の明細は非表示とするため、重複する明細をまとめる処理は不要。
        //       備考については、家庭教師の備考を表示する。

        // 請求年月の形式のバリデーションと変換
        $invoice_date = $this->fmYmToDate($date);

        // データ有無チェック（請求情報）
        Invoice::where('invoice.sid', $sid)
            // 生徒No・請求年月を指定
            ->where('invoice.invoice_date', $invoice_date)
            // MEMO: 取得できない場合はエラーとする
            ->firstOrFail();

        // クエリを作成
        $query = Invoice::query();

        // データを取得（請求情報）
        $invoice = $query
            // 生徒Noを指定
            ->where('invoice.sid', $sid)
            // 請求年月を指定
            ->where('invoice.invoice_date', $invoice_date)
            // データを取得
            ->select(
                'invoice.invoice_date',
                'ext_student_kihon.name as sname',
                'invoice.pay_type',
                'ext_generic_master.name1 as pay_name',
                'invoice_1.agreement as agreement1',
                'invoice_2.agreement as agreement2',
                'invoice.issue_date',
                'invoice.bill_date',
                'invoice.note'
            )
            // 支払方法名の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('invoice.pay_type', '=', 'ext_generic_master.code')
                    ->where('ext_generic_master.codecls', AppConst::EXT_GENERIC_MASTER_102);
            })
            // 生徒名の取得
            ->sdLeftJoin(ExtStudentKihon::class, 'invoice.sid', '=', 'ext_student_kihon.sid')
            // 自テーブルの個別教室レコードの契約内容を取得
            ->sdLeftJoin(Invoice::class, function ($join) {
                $join->on('invoice.sid', '=', 'invoice_1.sid');
                $join->on('invoice.invoice_date', '=', 'invoice_1.invoice_date')
                    ->where('invoice_1.lesson_type', AppConst::CODE_MASTER_8_1);
            }, 'invoice_1')
            // 自テーブルの家庭教師レコードの契約内容を取得
            ->sdLeftJoin(Invoice::class, function ($join) {
                $join->on('invoice.sid', '=', 'invoice_2.sid');
                $join->on('invoice.invoice_date', '=', 'invoice_2.invoice_date')
                    ->where('invoice_2.lesson_type', AppConst::CODE_MASTER_8_2);
            }, 'invoice_2')
            // 2レコードある場合でも1レコードにまとめる（家庭教師優先）
            ->orderBy('invoice.lesson_type', 'desc')
            ->first();


        $invoice['invoice_date'] = strtotime('2023-07-01');
        $invoice['pay_name'] = "口座引落";
        $invoice['issue_date'] = strtotime('2023-06-19');
        $invoice['bill_date'] = strtotime('2023-07-04');
        $invoice['agreement1'] = "7月分お月謝期間：7月10日（月）～8月5日（土）実施分となります。";
        $invoice['agreement2'] = "※7月21日（金）より夏期特別期間となります。";
        $invoice['note'] = "ご登録いただきました口座から引落をさせていただきます。";

        // クエリを作成（請求情報詳細）
        $query = InvoiceDetail::query();

        // データを取得（請求情報詳細）
        $invoiceDatails = $query
            // 生徒Noを指定
            ->where('invoice_detail.sid', $sid)
            // 請求年月を指定
            ->where('invoice_detail.invoice_date', $invoice_date)
            // データを取得
            ->select(
                'invoice_detail.cost_name',
                'invoice_detail.invoice_seq',
                'invoice_detail.cost'
            )
            // 金額0のデータは除外
            ->where('invoice_detail.cost', '!=', 0)
            //->where('invoice_detail.invoice_seq', 1)
            ->orderBy('lesson_type')
            ->orderBy('order_code')
            ->get();

        // 引落日表示有無
        // 請求方法追加に伴う修正（JC引落を引落日表示対象に追加する）
        if (
            $invoice->pay_type ==  AppConst::EXT_GENERIC_MASTER_102_4
            || $invoice->pay_type == AppConst::EXT_GENERIC_MASTER_102_5
            || $invoice->pay_type == AppConst::EXT_GENERIC_MASTER_102_6
            || $invoice->pay_type == AppConst::EXT_GENERIC_MASTER_102_7
        ) {
            $invoice['billflg'] = 1;
        } else {
            $invoice['billflg'] = 0;
        }

        $invoiceDatails[0]['cost_name'] = "7月分授業料";
        $invoiceDatails[0]['unit_cost'] = 8690;
        $invoiceDatails[0]['times'] = 8;
        $invoiceDatails[0]['cost'] = 69520;
        $invoiceDatails[1]['cost_name'] = "7月分授業料2";
        $invoiceDatails[1]['unit_cost'] = 6083;
        $invoiceDatails[1]['times'] = 8;
        $invoiceDatails[1]['cost'] = 48664;
        //$invoiceDatails[2]['cost_name'] = "入会金";
        //$invoiceDatails[2]['unit_cost'] = 0;
        //$invoiceDatails[2]['times'] = 0;
        //$invoiceDatails[2]['cost'] = 11000;

        // 金額合計の算出
        $invoice['cost_sum'] = 0;
        foreach ($invoiceDatails as $invoiceDatail) {
            $invoice['cost_sum'] = $invoice['cost_sum'] + $invoiceDatail->cost;
        }

        $invoiceData = [
            'invoice' => $invoice,
            'invoice_detail' => $invoiceDatails
        ];

        return $invoiceData;
    }

    /**
     * PDF出力処理 請求書
     * 
     * @param $id ID
     */
    private function outputPdfInvoice($data)
    {

        // PDFインスタンスを取得(P:縦向き)
        $pdf = new TCPDF("P", "mm", "A4", true, "UTF-8");

        // header/footerなし
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // ページを追加
        $pdf->addPage();

        // フォントの指定(事前にフォントをインストールする必要がある)
        $pdf->SetFont('ipaexg', '', 12);

        // PDFをHTML(blade)から作成
        $pdf->writeHTML(view("pdf.student.invoice", $data)->render());

        // 右上のロゴの表示(HTMLでは難しいのでここで指定)
        // writeHTMLの内容が長すぎる場合、2ページ目にも表示されるので、2ページ目が想定される場合は対応が必要
        $pdf->Image(resource_path('pdf/testea_logo1.png'), 140, 10, 45.0);
        //$pdf->Image(resource_path('pdf/free-dial.png'), 149, 30.5, 5.0);

        //$pdf->SetFont('ipaexg', '', 12);
        //$pdf->SetXY(148, 15);
        //$pdf->Write(1, '個別指導塾TESTEA（久我山校）');

        $pdf->SetFont('ipaexg', '', 11);
        $pdf->SetXY(138, 25);
        $pdf->Write(1, '個別指導塾TESTEA（久我山校）');

        $pdf->SetFont('ipaexg', '', 8);
        $pdf->SetXY(138, 30);
        $pdf->Write(1, '〒168-0082');

        $pdf->SetFont('ipaexg', '', 9);
        $pdf->SetXY(138, 34);
        $pdf->Write(1, '東京都杉並区久我山2-16-27');
        $pdf->SetXY(138, 38);
        $pdf->Write(1, '関口花園ビル2F');

        $pdf->SetFont('ipaexg', '', 8);
        $pdf->SetXY(138, 42);
        $pdf->Write(1, 'TEL 03-3335-2774 FAX 03-6324-9054');

        $date_str = $data['invoice']->invoice_date->format('Y年m月');
        $student_name = $data['invoice']->sname;

        // ファイル名
        $filename = Lang::get(
            'message.pdf.invoice.name',
            [
                'date' => $date_str,
                'name' => $student_name
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
