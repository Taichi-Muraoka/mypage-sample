<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 給与算出交通費情報View - モデル
 */
class SalaryTravelCostView extends Model
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
    protected $table = 'salary_travel_costs_view';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'salary_travel_costs_view_id';

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
        'salary_date',
        'tutor_id',
        'unit_price1',
        'times1',
        'amount1',
        'unit_price2',
        'times2',
        'amount2',
        'unit_price3',
        'times3',
        'amount3',
        'hour_work',
        'cost',
        'untaxed_cost',
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'salary_date' => 'date',
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
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
