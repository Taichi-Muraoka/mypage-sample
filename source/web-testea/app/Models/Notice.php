<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * お知らせ情報 - モデル
 */
class Notice extends Model
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
    protected $table = 'notices';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'notice_id';

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
        'title',
        'text',
        'notice_type',
        'adm_id',
        'campus_cd',
        'regist_time'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'campus_cd' => 'string',
        'regist_time' => 'date',
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
            'notice_id' => ['integer'],
            'title' => ['string', 'max:50'],
            'text' => ['string', 'max:1000'],
            'notice_type' => ['integer'],
            'adm_id' => ['integer'],
            'campus_cd' => ['string', 'max:2', 'digits:2'],
            'regist_time' => ['date_format:Y-m-d H:i:s']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------
    /**
     * 検索 校舎コード
     */
    public function scopeSearchCampusCd($query, $obj)
    {
        $key = 'campus_cd';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }
    /**
     * 検索 お知らせ種別
     */
    public function scopeSearchNoticeType($query, $obj)
    {
        $key = 'notice_type';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, $obj[$key]);
        }
    }

    /**
     * 検索 宛先種別
     */
    public function scopeSearchDestinationType($query, $obj)
    {
        $key = 'destination_type';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // 生徒IDでスケジュールを絞り込む(共通処理)
            $this->mdlWhereScheduleBySidQuery($query, self::class, $obj[$key]);
        }
    }

    /**
     * 検索 タイトル
     */
    public function scopeSearchTitle($query, $obj)
    {
        $key = 'title';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, 'LIKE',  '%' . $obj[$key] . '%');
        }
    }
}
