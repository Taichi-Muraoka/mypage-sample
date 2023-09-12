<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 給与算出情報 - モデル
 */
class SalarySummary extends Model
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
    protected $table = 'salary_summarys';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'salary_summary_id';

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
        'salary_date',
        'tutor_id',
        'summary_kind',
        'hour_payment',
        'hour',
        'amount',
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
            'salary_summary_id' => ['integer'],
            'salary_date' => ['date_format:Y-m-d'],
            'tutor_id' => ['integer'],
            'summary_kind' => ['integer', 'in:1,2,3,4,5,6,7,8,9,10'],
            'hour_payment' => ['integer', 'min:0', 'max:99999999'],
            'hour' => ['integer', 'min:0', 'max:999'],
            'amount' => ['integer', 'min:0', 'max:99999999'],
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
