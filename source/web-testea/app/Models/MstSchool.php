<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 学校マスタ - モデル
 */
class MstSchool extends Model
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
    protected $table = 'mst_schools';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'school_cd';

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
        'school_cd',
        'school_kind',
        'school_kind_cd',
        'pref_cd',
        'establish_kind',
        'branch_kind',
        'name',
        'address',
        'post_code',
        'setting_date',
        'abolition_date',
        'old_shool_cd',
        'change_flg'
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
            'school_cd' => ['string', 'max:13'],
            'school_kind' => ['string', 'max:2'],
            'school_kind_cd' => ['integer'],
            'pref_cd'=> ['string', 'max:2'],
            'establish_kind' => ['integer'],
            'branch_kind' => ['integer'],
            'name' => ['string', 'max:50'],
            'address' => ['string', 'max:100'],
            'post_code' => ['string', 'max:7'],
            'setting_date' => ['date_format:Y-m-d'],
            'abolition_date' => ['date_format:Y-m-d'],
            'old_shool_cd' => ['string', 'max:6'],
            'change_flg' => ['string', 'max:13'],
        ];
        return $_fieldRules;
    }
}
