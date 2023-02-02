<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 規定情報(月毎)（業務支援システム連携データ） - モデル
 */
class ExtRegular extends Model
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
    protected $table = 'ext_regular';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = [
        'roomcd',
        'sid',
        'r_seq'
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
    protected $fillable = [
        'roomcd',
        'sid',
        'r_seq',
        'startdate',
        'enddate',
        'regular_summary',
        'base_time',
        'updtime'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = ['startdate', 'enddate'];

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
            'roomcd' => ['string', 'max:4'],
            'sid' => ['integer', 'min:1', 'max:99999999'],
            'r_seq' => ['integer', 'min:1', 'max:999999999'],
            'startdate' => ['date_format:Y-m-d'],
            'enddate' => ['date_format:Y-m-d'],
            'regular_summary' => ['string', 'max:100'],
            'tuition' => ['integer', 'min:-99999999', 'max:99999999'],
            'base_tuition' => ['integer', 'min:-99999999', 'max:99999999'],
            'base_time' => ['integer', 'min:0', 'max:999'],

            // CSV取り込み向け
            'startdate_csv' => ['date_format:Y/m/d'],
            'enddate_csv' => ['date_format:Y/m/d'],
            'updtime_csv' => ['date_format:Y/m/d H:i:s']
        ];
        return $_fieldRules;
    }
}
