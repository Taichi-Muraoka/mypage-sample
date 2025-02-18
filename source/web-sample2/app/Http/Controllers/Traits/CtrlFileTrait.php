<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Http\Request;
use App\Exceptions\ReadDataValidateException;
use Illuminate\Support\Facades\Lang;

/**
 * File - コントローラ共通処理
 */
trait CtrlFileTrait
{

    //==========================
    // ファイルアップロード
    //==========================

    /**
     * ファイルアップロードされたかチェック
     *
     * @param $request リクエスト 
     * @param $fieldName 項目名
     */
    protected function fileUploadCheck(Request $request, $fieldName)
    {
        return $request->hasFile('file_' . $fieldName);
    }

    /**
     * ファイルアップロード時に値をセット
     *
     * @param $request リクエスト 
     * @param $fieldName 項目名
     */
    protected function fileUploadSetVal(Request $request, $fieldName)
    {
        // ファイルアップロード時は、「file_項目名」でファイルがアップロードされる
        if ($request->hasFile('file_' . $fieldName)) {
            // 元の項目名にアップロードされたファイル名をセットする
            $request->merge([$fieldName => $request->{'file_' . $fieldName}->getClientOriginalName()]);
        } else {
            // 空白で送信するケースもあるのでバリデーションで拾ってもらうので以下は不要
            //throw new Exception(__METHOD__ . 'no file name: ' . $fieldName);
        }
    }

    /**
     * ファイルアップロードされたファイルのパスを取得(tmp)
     * 
     * @param $request リクエスト 
     * @param $fieldName 項目名
     */
    protected function fileUploadRealPath(Request $request, $fieldName)
    {
        // ファイルアップロード時は、「file_項目名」でファイルがアップロードされる
        if ($request->hasFile('file_' . $fieldName)) {
            return $request->{'file_' . $fieldName}->getRealPath();
        } else {
            // ここではエラーとする
            throw new Exception(__METHOD__ . 'no file name: ' . $fieldName);
        }
    }

    /**
     * アップロードされたファイルを保存
     * 
     * @param $request リクエスト 
     * @param $dir 保存先 
     * @param $fieldName 項目名
     * @return string path
     */
    protected function fileUploadSave(Request $request, $dir, $fieldName)
    {
        try {
            // ファイルアップロード時は、「file_項目名」でファイルがアップロードされる
            $fileId = 'file_' . $fieldName;

            if ($request->hasFile($fileId)) {

                // アップロードされたファイル名そのままを保存する。上書き保存
                $filename = $request->{$fileId}->getClientOriginalName();
                $path = $request->{$fileId}->storeAs($dir, $filename);

                // フルパスを返却
                return Storage::path($path);
            } else {
                // 編集時にアップしないケースもあるのでエラーにしない
                //throw new Exception(__METHOD__ . ' no file name: ' . $fieldName);
            }
        } catch (\Exception  $e) {
            // ファイル名が長い場合の対策など、エラー時は不正として処理する
            $this->illegalResponseErr();
        }
    }

    //==========================
    // ファイルダウンロード
    //==========================

    /**
     * ファイル名をダウンロードできるファイル名に変換する
     * 
     * @param string $fileName ファイル名
     * @return string ファイル名
     */
    protected function convFileName($fileName)
    {

        // ホワイトスペース相当の文字をアンダースコアに
        $fileName = preg_replace('/\\s/u', '_', $fileName);
        // ファイル名に使えない文字をアンダースコアに
        $fileName = str_replace(array('\\', '/', ':', '*', '?', '"', '<', '>', '|'), '_', $fileName);

        return $fileName;
    }

    /**
     * ファイルダウンロード ヘッダーのセット
     * 
     * @param string $fileName ファイル名
     * @param bool $csvSjis 出力がSJISのCSVの場合
     */
    protected function fileDownloadHeader($fileName, $csvSjis = false)
    {
        // ファイル名を変換
        $fileName = $this->convFileName($fileName);

        header("Pragma: public");
        header("Expires: 0 ");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Transfer-Encoding: binary");
        header("Content-Type: application/octet-streams");

        if ($csvSjis) {
            // カスタムヘッダを指定する。CSVをSJISで出力する場合を考慮した。
            // このcharsetを判断してJavascriptでSJISに変換する処理を行う。
            // JavascriptでSJIS固定でも良かったのだが、一応明示的に応答するようにした
            header("content-to-sjis: text/csv; charset=Shift_JIS");
        }

        // ファイル名日本語対応 RFC2231形式（最近のブラウザならば対応している）
        header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($fileName));
    }

    //==========================
    // Zip
    //==========================

    /**
     * Zipファイルを解凍する
     * 
     * @param string $zipPath Zipファイルのパス
     * @return array ファイル名のリスト
     */
    protected function unzip($zipPath)
    {
        // Zipファイルを扱う
        $zip = new \ZipArchive();
        $res = $zip->open($zipPath);

        // 失敗した時は例外
        if ($res != true) {
            throw new ReadDataValidateException(Lang::get('validation.read_file_error'));
        }

        // 一時フォルダにフォルダを作って解凍する
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(time() . rand()) . DIRECTORY_SEPARATOR;

        // 解凍
        $zip->extractTo($tmpDir);
        $zip->close();

        // ファイル一覧の取得(ディレクトリは確認しない)
        $opPathList = [];
        $filelist = glob($tmpDir . '*');

        // ファイルのリストを取得
        foreach ($filelist as $file) {
            if (is_file($file)) {
                array_push($opPathList, $file);
            }
        }

        return $opPathList;
    }

    /**
     * Zipファイルを解凍したファイルを削除する
     * tmpディレクトリに作成するのでいずれは消えるが明示的に削除する
     * ファイルしか消されない点に注意
     * 
     * @param array $opPathList unzipの戻り値
     */
    protected function unzipCleanUp($opPathList)
    {
        // ファイルリストを削除する
        foreach ($opPathList as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
