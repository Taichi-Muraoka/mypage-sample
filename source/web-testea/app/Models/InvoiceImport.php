<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 請求取込情報 - モデル
 */
class InvoiceImport extends Model
{

    // モデルの共通処理
    use \App\Traits\ModelTrait;

    // 論理削除
    use SoftDeletes;

    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'invoice_import';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'invoice_date';

    /**
     * IDが自動増分されるか
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'invoice_date' => 'date',
        'issue_date' => 'date',
        'bill_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'import_date' => 'date',
    ];

    /**
     * 属性に対するモデルのデフォルト値
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * 配列に含めない属性
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * モデルの「初期起動」メソッド
     *
     * @return void
     */
    protected static function booted()
    {
        // 更新時、空白をnullに変換する処理
        self::whenSaveEmptyToNull();

        // テーブル操作時、ログを残す処理
        self::saveToLog();
    }

    //-------------------------------
    // 項目定義
    //-------------------------------

    /**
     * テーブル項目の定義
     *
     * @return array
     */
    protected static function getFieldRules()
    {
        static $_fieldRules = [
            'invoice_date' => ['date_format:Y-m-d'],
            'issue_date' => ['date_format:Y-m-d'],
            'bill_date' => ['date_format:Y-m-d'],
            'start_date' => ['date_format:Y-m-d'],
            'end_date' => ['date_format:Y-m-d'],
            'term_text1' => ['string', 'max:50'],
            'term_text2' => ['string', 'max:50'],
            'import_state' => ['integer', 'in:0,1'],
            'import_date' => ['date_format:Y-m-d H:i:s']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
