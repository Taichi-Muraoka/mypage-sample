<?php

namespace App\Http\Controllers\Traits;

use TCPDF;
use Illuminate\Support\Facades\Lang;
use App\Consts\AppConst;
use App\Models\InvoiceDetail;
use App\Models\CodeMaster;
use App\Models\Invoice;
use App\Models\InvoiceImport;
use App\Models\MstCampus;
use App\Models\Student;

/**
 * 請求書 - 機能共通処理
 */
trait FuncInvoiceTrait
{

    /**
     * 請求情報の取得
     *
     * @param string date 請求年月（yyyymm）
     * @param string sid 生徒ID
     * @return array
     */
    private function getInvoiceDetail($date, $sid)
    {
        // 請求年月の形式のバリデーションと変換
        $invoice_date = $this->fmYmToDate($date);

        // 校舎名取得(JOIN)
        $campus_names = $this->mdlGetRoomQuery();

        //-------------------------
        // 請求取込情報の取得
        //-------------------------
        // クエリを作成
        $query = InvoiceImport::query();

        // データを取得
        $invoiceImport = $query
            // 請求年月を指定
            ->where('invoice_import.invoice_date', $invoice_date)
            // データを取得
            ->select(
                'invoice_import.issue_date',
                'invoice_import.bill_date',
                'invoice_import.term_text1',
                'invoice_import.term_text2',
            )
            ->firstOrFail();

        //-------------------------
        // 請求情報の取得
        //-------------------------
        // クエリを作成
        $query = Invoice::query();

        // データを取得
        $invoice = $query
            // 生徒IDを指定
            ->where('invoices.student_id', $sid)
            // 請求年月を指定
            ->where('invoices.invoice_date', $invoice_date)
            // データを取得
            ->select(
                'invoices.invoice_id',
                'invoices.student_id',
                'invoices.invoice_date',
                'invoices.campus_cd',
                'invoices.pay_type',
                'invoices.total_amount',
                // 生徒名
                'students.name as student_name',
                // 校舎名
                'campus_names.room_name as campus_name',
                // 校舎のメールアドレス
                'mst_campuses.email_campus',
                // コードマスタの名称（支払方法）
                'mst_codes.name as pay_type_name',
            )
            // 生徒名の取得
            ->sdLeftJoin(Student::class, 'invoices.student_id', '=', 'students.student_id')
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('invoices.campus_cd', '=', 'campus_names.code');
            })
            // 校舎メールアドレスの取得
            ->sdLeftJoin(MstCampus::class, 'invoices.campus_cd', '=', 'mst_campuses.campus_cd')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('invoices.pay_type', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_21);
            })
            ->firstOrFail();

        //-------------------------
        // 請求明細情報の取得
        //-------------------------
        // クエリを作成
        $query = InvoiceDetail::query();

        // データを取得
        $invoiceDatails = $query
            // 請求書IDを指定（上記で取得済）
            ->where('invoice_details.invoice_id', $invoice->invoice_id)
            // データを取得
            ->select(
                'invoice_details.description',
                'invoice_details.unit_price',
                'invoice_details.times',
                'invoice_details.amount',
            )
            // ソート連番順
            ->orderBy('invoice_seq')
            ->get();

        // 支払方法による備考文言分岐
        if ($invoice->pay_type ==  AppConst::CODE_MASTER_21_1) {
            // 口座引落ならフラグを1にする
            $invoice['billflg'] = 1;
            $invoice['note'] = "ご登録いただきました口座から引落をさせていただきます。";
        } else {
            // 振込の場合
            $invoice['billflg'] = 2;
            $invoice['note'] = "お振込先\n" .
                "三菱UFJ銀行　久我山支店（567）\n" .
                "普通口座　0043210\n" .
                "個別指導塾テスティー株式会社\n" .
                "\n" .
                "お振込者名は生徒様のお名前で入力をお願いいたします。\n" .
                "恐れ入りますが、振込手数料はご負担ください。";
        }

        $invoiceData = [
            'invoice_import' => $invoiceImport,
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
        $pdf->Image(resource_path('pdf/testea_logo.png'), 150, 10, 48.0);

        $pdf->SetFont('ipaexg', '', 10);
        $pdf->SetXY(148, 26);
        $pdf->Write(1, '個別指導塾テスティー株式会社');

        $pdf->SetFont('ipaexg', '', 7);
        $pdf->SetXY(148, 31);
        $pdf->Write(1, '登録番号：T1011301018175');

        $pdf->SetFont('ipaexg', '', 7);
        $pdf->SetXY(148, 35.5);
        $pdf->Write(1, '〒168-0082');

        $pdf->SetFont('ipaexg', '', 8);
        $pdf->SetXY(148, 39);
        $pdf->Write(1, '東京都杉並区久我山2-16-27');
        $pdf->SetXY(148, 43);
        $pdf->Write(1, '関口花園ビル2F');

        $date_str = $data['invoice']->invoice_date->format('Y年m月');
        $student_name = $data['invoice']->student_name;

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
