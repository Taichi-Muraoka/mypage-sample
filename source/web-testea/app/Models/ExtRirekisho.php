<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 履歴書（業務支援システム連携データ） - モデル
 */
class ExtRirekisho extends Model
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
    protected $table = 'ext_rirekisho';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = 'tid';

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
    protected $fillable = [
        'tid'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = [];

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
            // decimal(6,0)なので6桁の制限とした
            'tid' => ['integer', 'min:1', 'max:999999'],
            'name' => ['string', 'max:50'],
            // emailバリデーション・maxも追加
            'mailaddress1' => ['string', 'email:rfc,filter', 'max:100'],

            // CSV取り込み向け
            'updtime_csv' => ['date_format:Y/m/d H:i:s']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 tid
     */
    public function scopeSearchTid($query, $obj)
    {
        $key = 'tid';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, '=', $obj[$key]);
        }
    }

    /**
     * 検索 name
     */
    public function scopeSearchName($query, $obj)
    {
        $key = 'name';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // nameが他とかぶるので、テーブル名を指定した
            $query->where($this->getTable() . '.' . $key, 'LIKE',  '%' . $obj[$key] . '%');
        }
    }
}
