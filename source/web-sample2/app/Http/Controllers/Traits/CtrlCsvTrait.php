<?php

namespace App\Http\Controllers\Traits;

use \SplFileObject;

/**
 * CSV - コントローラ共通処理
 */
trait CtrlCsvTrait
{

    //==========================
    // CSV操作
    //==========================

    /**
     * CSVの読み込み
     * 
     * @param string $path パス
     * @return SplFileObject $file
     */
    protected function readCsv($path, $code = "utf8")
    {

        if ($code == "sjis") {
            //-------------
            // SJISの場合
            //-------------

            // SJISのまま読み込むと、うまくSplFileObjectが扱ってくれない・・
            // しょうがないので一旦、tmpに保存する
            $data = file_get_contents($path);
            $data = mb_convert_encoding($data, 'UTF-8', 'SJIS-win');
            // tmpファイルの作成。終わったら削除される
            $temp = tmpfile();
            $meta = stream_get_meta_data($temp);
            fwrite($temp, $data);
            rewind($temp);

            // ファイルパス
            $path = $meta['uri'];
        }

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

        return $file;
    }

    /**
     * CSVの出力(レスポンスを返す)
     * 
     * @param array $array CSVにする配列
     * @param bool $crlf 改行コード true: CRLF
     */
    protected function outputCsv($array, $crlf = true)
    {
        
        // MEMO: SJISの出力はここでは行わない
        // 今回axiosを使用して、非同期でデータを取得→ファイルのダウンロードにしたが、
        // axiosが勝手にUTF-8に変換してしまうようで、Javascriptの方で変換するようにした。
        // なお、非同期ではなく、直接CSVダウンロードさせたい場合は、ここでSJISへの変換の必要がある。
        // HTTPヘッダーにcharset=Shift_JISを指定する。JSではそれを判断して変換する

        if ($crlf) {
            // 末尾改行コードをCR+LFにするフィルタクラスをストリームフィルタとして登録
            stream_filter_register('CrlfFilter', CrlfFilter::class);
            // フィルターを指定
            $file = new SplFileObject('php://filter/write=CrlfFilter/resource=php://output', 'w');
        } else {
            // 通常出力。改行コードはLFになる
            $file = new \SplFileObject('php://output', 'w');
        }

        // 配列をCSV出力
        foreach ($array as $row) {
            $file->fputcsv($row);
        }

        // 書き込み
        $file->fflush();

        // exitしないと勝手にレスポンスヘッダーを変えられてしまうため
        // text/html; charset=UTF-8が勝手につけられる
        exit();
    }
}

/**
 * CSVファイル出力時のフィルター
 * 改行コードをCRLFに変換する
 */
class CrlfFilter
{
    /**
     * filter
     * 改行コードをCRLFに変換する
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {

            // 改行コードをCRLFに変換する
            $bucket->data = preg_replace("/\n$/", '', $bucket->data);
            $bucket->data = preg_replace("/\r$/", '', $bucket->data);
            $bucket->data = $bucket->data . "\r\n";

            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}
