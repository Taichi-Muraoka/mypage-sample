<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 問い合わせ情報 - モデル
 */
class Contact extends Model
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
    protected $table = 'contact';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = 'contact_id';

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
        'sid',
        'title',
        'text',
        'roomcd',
        'regist_time',
        'contact_state',
        'answer_time',
        'answer_text',
        'adm_id',
        'contact_id',
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
            'roomcd' => ['string'],
            'title' => ['string', 'max:100'],
            'text' => ['string', 'max:1000'],
            'regist_time' => ['date_format:Y-m-d'],
            'sid' => ['integer'],
            'adm_id' => ['integer'],
            'answer_time' => ['date_format:Y-m-d'],
            'answer_text' => ['string', 'max:1000'],
            'contact_state' => ['integer']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 roomcd
     */
    public function scopeSearchRoomcd($query, $obj)
    {
        $key = 'roomcd';
        $col = 'contact.roomcd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 contact_state
     */
    public function scopeSearchContactStates($query, $obj)
    {
        $key = 'contact_state';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, $obj[$key]);
        }
    }
}
