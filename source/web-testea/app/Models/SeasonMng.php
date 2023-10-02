<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 特別期間講習管理 - モデル
 */
class SeasonMng extends Model
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
    protected $table = 'season_mng';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'season_mng_id';

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
        'season_cd',
        'campus_cd',
        't_start_date',
        't_end_date',
        's_start_date',
        's_end_date',
        'lesson_times',
        'confirm_date',
        'status'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [];

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
            'season_mng_id' => ['integer'],
            'season_cd' => ['string', 'max:6'],
            'campus_cd' => ['string', 'max:2'],
            't_start_date' => ['date_format:Y-m-d'],
            't_end_date' => ['date_format:Y-m-d'],
            's_start_date' => ['date_format:Y-m-d'],
            's_end_date' => ['date_format:Y-m-d'],
            'lesson_times' => ['integer', 'min:0', 'max:99'],
            'confirm_date' => ['date_format:Y-m-d'],
            'status' => ['integer', 'in:0,1']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
