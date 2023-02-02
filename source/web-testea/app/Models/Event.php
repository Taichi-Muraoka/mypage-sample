<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * イベント情報 - モデル
 */
class Event extends Model
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
    protected $table = 'event';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = 'event_id';

    /**
     * IDが自動増分されるか
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'cls_cd',
        'event_date',
        'start_time',
        'end_time'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = [
        'event_date',
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
     *
     * @return array
     */
    protected static function getFieldRules()
    {
        static $_fieldRules = [
            'event_id' => ['integer'],
            'name' => ['string', 'max:100'],
            'cls_cd' => ['string', 'max:2'],
            'event_date' => ['date_format:Y-m-d'],
            'start_time' => ['vdTime'],
            'end_time' => ['vdTime']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 イベント名
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
     * 検索 開催日From
     */
    public function scopeSearchEventDateFrom($query, $obj)
    {
        $key = 'event_date_from';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('event_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '>=', $obj[$key]);
        }
    }

    /**
     * 検索 開催日To
     */
    public function scopeSearchEventDateTo($query, $obj)
    {
        $key = 'event_date_to';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('event_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '<=', $obj[$key]);
        }
    }
}
