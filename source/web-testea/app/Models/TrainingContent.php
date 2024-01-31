<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 研修資料 - モデル
 */
class TrainingContent extends Model
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
    protected $table = 'training_contents';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'trn_id';

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
        'trn_type',
        'text',
        'url',
        'regist_time',
        'release_date',
        'limit_date'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'regist_time' => 'date',
        'release_date' => 'date',
        'limit_date' => 'date',
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
            'trn_id' => ['integer'],
            'trn_type' => ['integer'],
            'text' => ['string', 'max:100'],
            'url' => ['string', 'max:1000'],
            'regist_time' => ['date_format:Y-m-d'],
            'release_date' => ['date_format:Y-m-d'],
            'limit_date' => ['date_format:Y-m-d']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 形式
     */
    public function scopeSearchTrnType($query, $obj)
    {
        $key = 'trn_type';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, '=', $obj[$key]);
        }
    }

    /**
     * 検索 登録日
     */
    public function scopeSearchRegistTime($query, $obj)
    {
        $key = 'regist_time';
        // 登録日がtimestampなのでYmdに変換して検索する
        $col = $this->mdlFormatYmd('regist_time');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '=', $obj[$key]);
        }
    }

    /**
     * 検索 研修内容
     */
    public function scopeSearchText($query, $obj)
    {
        $key = 'text';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, 'LIKE', '%' . $obj[$key] . '%');
        }
    }
}
