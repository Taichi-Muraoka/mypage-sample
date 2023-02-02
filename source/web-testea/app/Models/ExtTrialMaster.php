<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 模試マスタ（業務支援システム連携データ） - モデル
 */
class ExtTrialMaster extends Model
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
    protected $table = 'ext_trial_master';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = 'tmid';

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
    protected $dates = [
        'trial_date',
        'start_time',
        'end_time'
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
     */
    protected static function getFieldRules()
    {
        static $_fieldRules = [
            'tmid' => ['integer'],
            'name' => ['string', 'max:60'],
            'symbol' => ['string'],
            'cls_cd' => ['string', 'max:2'],
            'price' => ['integer'],
            'trial_date' => ['date_format:Y-m-d'],
            'disp_flg' => ['integer'],

            // CSV取り込み向け
            'trial_date_csv' => ['date_format:Y/m/d'],
            'start_time_csv' => ['date_format:H:i:s'],
            'end_time_csv' => ['date_format:H:i:s'],
            'updtime_csv' => ['date_format:Y/m/d H:i:s']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 模試名
     */
    public function scopeSearchName($query, $obj)
    {
        $key = 'name';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, 'LIKE', '%' . $obj[$key] . '%');
        }
    }

    /**
     * 検索 学年
     */
    public function scopeSearchClsCd($query, $obj)
    {
        $key = 'cls_cd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, '=', $obj[$key]);
        }
    }

    /**
     * 検索 試験日From
     */
    public function scopeSearchTrialDateFrom($query, $obj)
    {
        $key = 'trial_date_from';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('trial_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '>=', $obj[$key]);
        }
    }

    /**
     * 検索 試験日To
     */
    public function scopeSearchTrialDateTo($query, $obj)
    {
        $key = 'trial_date_to';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('trial_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '<=', $obj[$key]);
        }
    }
}
