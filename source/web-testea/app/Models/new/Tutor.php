<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 講師情報 - モデル
 */
class Tutor extends Model
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
    protected $table = 'tutors';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'tutor_id';

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
        'name',
        'name_kana',
        'tel',
        'email',
        'address',
        'birth_date',
        'gender_cd',
        'grade_cd',
        'grade_year',
        'school_cd_j',
        'school_cd_h',
        'school_cd_u',
        'hourly_base_wage',
        'tutor_status',
        'enter_date',
        'leave_date',
        'memo'
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
            'tutor_id' => ['integer'],
            'name' => ['string', 'max:50'],
            'name_kana' => ['string', 'max:50'],
            'tel' => ['string', 'max:20'],
            'email' => ['string', 'email:rfc,filter', 'max:100'],
            'address' => ['string', 'max:100'],
            'birth_date' => ['date_format:Y-m-d'],
            'gender_cd' => ['integer', 'in:1,2,9'],
            'grade_cd' => ['integer'],
            'grade_year' => ['string', 'max:4'],
            'school_cd_j' => ['string', 'max:13'],
            'school_cd_h' => ['string', 'max:13'],
            'school_cd_u' => ['string', 'max:13'],
            'hourly_base_wage' => ['integer', 'min:0', 'max:9999'],
            'gender_cd' => ['integer', 'in:1,2,3'],
            'enter_date' => ['date_format:Y-m-d'],
            'leave_date' => ['date_format:Y-m-d'],
            'memo' => ['string', 'max:1000']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
