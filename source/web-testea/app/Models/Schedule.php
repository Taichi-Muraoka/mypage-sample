<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * スケジュール情報 - モデル
 */
class Schedule extends Model
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
    protected $table = 'schedules';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'schedule_id';

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
        'campus_cd',
        'target_date',
        'period_no',
        'start_time',
        'end_time',
        'minites',
        'booth_cd',
        'course_cd',
        'student_id',
        'tutor_id',
        'subject_cd',
        'create_kind',
        'lesson_kind',
        'how_to_kind',
        'substitute_kind',
        'absent_tutor_id',
        'absent_status',
        'tentative_status',
        'regular_class_id',
        'transfer_id',
        'transfer_class_id',
        'report_id',
        'memo',
        'adm_id'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'campus_cd' => 'string',
        'booth_cd' => 'string',
        'course_cd' => 'string',
        'subject_cd' => 'string',
        'target_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i'
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
            'schedule_id' => ['integer'],
            'campus_cd' => ['string', 'max:2', 'digits:2'],
            'target_date' => ['date_format:Y-m-d'],
            'period_no' => ['integer', 'min:0', 'max:99'],
            'start_time' => ['vdTime'],
            'end_time' => ['vdTime', 'after:start_time'],
            'minites' => ['integer', 'min:0', 'max:999'],
            'booth_cd' => ['string', 'max:3', 'digits:3'],
            'course_cd' => ['string', 'max:5', 'digits:5'],
            'student_id' => ['integer'],
            'tutor_id' => ['integer'],
            'subject_cd' => ['string', 'max:3', 'digits:3'],
            'create_kind' => ['integer'],
            'lesson_kind' => ['integer'],
            'how_to_kind' => ['integer'],
            'substitute_kind' => ['integer'],
            'absent_tutor_id' => ['integer'],
            'absent_status' => ['integer', ],
            'tentative_status' => ['integer'],
            'regular_class_id' => ['integer'],
            'transfer_id' => ['integer'],
            'transfer_class_id' => ['integer'],
            'report_id' => ['integer'],
            'memo' => ['string', 'max:1000'],
            'adm_id' => ['integer']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
