<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 講師情報View - モデル
 */
class TutorView extends Model
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
    protected $table = 'tutors_view';

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
    public $incrementing = false;

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
        'enter_term',
        'memo'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'birth_date' => 'date',
        'grade_year' => 'string',
        'school_cd_j' => 'string',
        'school_cd_h' => 'string',
        'school_cd_u' => 'string'
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
