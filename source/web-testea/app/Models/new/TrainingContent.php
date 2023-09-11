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
            'trn_id' => ['integer'],
            'trn_type' => ['integer', 'in:1,2'],
            'text' => ['string', 'max:1000'],
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

}
