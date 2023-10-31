<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 生徒情報View - モデル
 */
class StudentView extends Model
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
    protected $table = 'students_view';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'student_id';

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
        'grade_cd',
        'grade_year',
        'birth_date',
        'school_cd_e',
        'school_cd_j',
        'school_cd_h',
        'is_jukensei',
        'tel_stu',
        'tel_par',
        'email_stu',
        'email_par',
        'login_kind',
        'stu_status',
        'enter_date',
        'leave_date',
        'recess_start_date',
        'recess_end_date',
        'past_enter_term',
        'enter_term',
        'lead_id',
        'storage_link',
        'memo'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'school_cd_e' => 'string',
        'school_cd_j' => 'string',
        'school_cd_h' => 'string',
        'enter_date' => 'date',
        'leave_date' => 'date',
        'recess_start_date' => 'date',
        'recess_end_date' => 'date',
        'lead_id' => 'string',
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
