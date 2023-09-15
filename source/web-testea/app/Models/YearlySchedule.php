<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 教室年間予定情報 - モデル
 */
class YearlySchedule extends Model
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
    protected $table = 'yearly_schedules';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'yearly_schedule_id';

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
        'school_year',
        'campus_cd',
        'lesson_date',
        'day_cd',
        'date_kind',
        'school_month',
        'week_count'
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
            'yearly_schedule_id' => ['integer'],
            'school_year' => ['string', 'max:4'],
            'campus_cd' => ['string', 'max:2'],
            'lesson_date' => ['date_format:Y-m-d'],
            'day_cd' => ['integer', 'in:1,2,3,4,5,6,7'],
            'date_kind' => ['integer', 'in:0,1,2,3,9'],
            'school_month' => ['string', 'max:2'],
            'week_count' => ['integer'],
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
