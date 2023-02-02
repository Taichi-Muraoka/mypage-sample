<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 汎用マスタ（業務支援システム連携データ） - モデル
 */
class ExtGenericMaster extends Model
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
    protected $table = 'ext_generic_master';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = [
        'codecls',
        'code'
    ];

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
            'codecls' => ['string', 'max:3'],
            'code' => ['string', 'max:20'],
            'value1' => ['string', 'max:10'],
            'value2' => ['string', 'max:10'],
            'value3' => ['string', 'max:10'],
            'value4' => ['string', 'max:10'],
            'value5' => ['string', 'max:10'],
            'name1' => ['string', 'max:80'],
            'name2' => ['string', 'max:80'],
            'name3' => ['string', 'max:80'],
            'name4' => ['string', 'max:80'],
            'name5' => ['string', 'max:80'],
            'disp_order' => ['numeric'],

            // CSV取り込み向け
            'updtime_csv' => ['date_format:Y/m/d H:i:s']
        ];

        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 codecls
     */
    public function scopeSearchCodecls($query, $obj)
    {
        $key = 'codecls';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, '=', $obj[$key]);
        }
    }
}
