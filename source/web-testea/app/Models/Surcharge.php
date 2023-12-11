<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 追加請求情報 - モデル
 */
class Surcharge extends Model
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
    protected $table = 'surcharges';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'surcharge_id';

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
        'tutor_id',
        'campus_cd',
        'apply_date',
        'surcharge_kind',
        'working_date',
        'start_time',
        'minutes',
        'tuition',
        'comment',
        'approval_status',
        'payment_date',
        'payment_status',
        'admin_comment'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'campus_cd' => 'string',
        'apply_date' => 'date',
        'working_date' => 'date',
        'start_time' => 'datetime:H:i',
        'payment_date' => 'date',
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
            'surcharge_id' => ['integer'],
            'tutor_id' => ['integer'],
            'campus_cd' => ['string', 'max:2', 'digits:2'],
            'apply_date' => ['date_format:Y-m-d'],
            'surcharge_kind' => ['integer'],
            'working_date' => ['date_format:Y-m-d'],
            'start_time' => ['vdTime'],
            'minutes' => ['integer', 'min:0', 'max:9999'],
            'tuition' => ['integer', 'min:0', 'max:99999999'],
            'comment' => ['string', 'max:1000'],
            'approval_status' => ['integer'],
            'payment_date' => ['date_format:Y-m-d'],
            'payment_status' => ['integer'],
            'admin_comment' => ['string', 'max:1000'],
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
