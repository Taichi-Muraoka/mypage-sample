<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ギフトカード - モデル
 */
class Card extends Model
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
    protected $table = 'card';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = 'card_id';

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
        'card_id',
        'grant_time',
        'apply_time',
        'card_state',
        'sid',
        'card_name',
        'discount',
        'term_start',
        'term_end',
        'comment'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = [
        'grant_time',
        'term_start',
        'term_end',
        'apply_time'
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
            'card_id' => ['integer'],
            'sid' => ['integer', 'min:1', 'max:99999999'],
            'card_name' => ['string', 'max:100'],
            'discount' => ['string', 'max:100'],
            'term_start' => ['date_format:Y-m-d'],
            'term_end' => ['date_format:Y-m-d'],
            'grant_time' => ['date_format:Y-m-d'],
            'apply_time' => ['date_format:Y-m-d'],
            'card_state' => ['integer', 'between:0,3'],
            'comment' => ['string', 'max:1000']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 生徒(sid)に紐づく教室
     */
    public function scopeSearchRoom($query, $obj)
    {
        $key = 'roomcd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // sidで教室で絞り込む(共通処理)
            $this->mdlWhereSidByRoomQuery($query, self::class, $obj[$key]);
        }
    }

    /**
     * 検索 ギフトカード状態
     */
    public function scopeSearchCardState($query, $obj)
    {
        $key = 'card_state';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, '=', $obj[$key]);
        }
    }

    /**
     * 検索 apply_time(From)
     */
    public function scopeSearchApplyTimeFrom($query, $obj)
    {
        $key = 'apply_time_from';
        // 申請日がDateTimeなのでYmdに変換して検索する
        $col = $this->mdlFormatYmd('apply_time');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '>=', $obj[$key]);
        }
    }

    /**
     * 検索 apply_time(To)
     */
    public function scopeSearchApplyTimeTo($query, $obj)
    {
        $key = 'apply_time_to';
        // 申請日がDateTimeなのでYmdに変換して検索する
        $col = $this->mdlFormatYmd('apply_time');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '<=', $obj[$key]);
        }
    }
}
