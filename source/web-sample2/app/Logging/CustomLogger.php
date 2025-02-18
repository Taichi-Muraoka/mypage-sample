<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Auth;

/**
 * カスタムロガー
 * ログのフォーマットを変更する
 * →IPアドレス、ログインID、ソース、行数などを追加
 */
class CustomLogger
{
    /**
     * ログフォーマット
     * 
     * datetime: 時刻
     * channel: チャンネル(local)
     * level_name: ログレベル
     * message: メッセージ
     * context: ログに指定される \Log::info("xx", ["XX" => "コンテキスト"]);
     * extra: このカスタムログで追加される情報
     */
    // 参考までにデフォルト
    //private $logFormat = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
    private $logFormat = "[%datetime%] [IP:%extra.ip%, URL:%extra.url%, account_id:%extra.account_id%, account_type:%extra.account_type%] " .
        "%channel%.%level_name%: " .
        "%extra.class%@%extra.function%(%extra.line%) - %message% %context%\n";

    /**
     * 日付フォーマット
     */
    private $dateFormat = 'Y-m-d H:i:s.v';

    /**
     * カスタムMonologインスタンスの生成
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        // フォーマッター
        $formatter = new LineFormatter($this->logFormat, $this->dateFormat, true, true);

        // エラー時にスタックトレースを出力する(contextに入ってくる)
        // userId, exception
        $formatter->includeStacktraces(true);

        // ログ出力ファイル(file, line, class, function)
        $ip = new IntrospectionProcessor(Logger::DEBUG, ['Illuminate\\']);

        // アクセス情報(url, ip, http_method, server, referer)
        $wp = new WebProcessor();

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($formatter);
            $handler->pushProcessor($ip);
            $handler->pushProcessor($wp);
            // ログに情報を追加する(ログインID)
            $handler->pushProcessor([$this, 'addExtraData']);
        }
    }

    /**
     * ログに情報を追加する
     *
     * @return array
     */
    public function addExtraData(LogRecord $record): LogRecord
    {
        // ログインしているかチェック
        $account_id = 'Not logged in.';
        $account_type = '';
        if (Auth::check()) {
            $account = Auth::user();
            $account_id = $account->account_id;
            $account_type = $account->account_type;
        }

        // アカウントのキーを取得
        $record->extra += [
            // アカウントID
            'account_id' => $account_id,
            // アカウント種別
            'account_type' => $account_type
        ];
        return $record;
    }
}
