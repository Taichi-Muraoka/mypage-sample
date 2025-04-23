<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * レギュラー授業情報 - モデル
 */
class RegularClass extends Model
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
    protected $table = 'regular_classes';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'regular_class_id';

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
        'day_cd',
        'period_no',
        'start_time',
        'end_time',
        'minutes',
        'booth_cd',
        'course_cd',
        'student_id',
        'tutor_id',
        'subject_cd',
        'how_to_kind'
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
            'regular_class_id' => ['integer'],
            'campus_cd' => ['string', 'max:2', 'digits:2'],
            'day_cd' => ['integer'],
            'period_no' => ['integer', 'min:0', 'max:99'],
            'start_time' => ['vdTime'],
            'end_time' => ['vdTime', 'vd_after_time:start_time'],
            'minutes' => ['integer', 'min:0', 'max:999'],
            'booth_cd' => ['string', 'max:3', 'digits:3'],
            'course_cd' => ['string', 'max:5', 'digits:5'],
            'student_id' => ['integer'],
            'tutor_id' => ['integer'],
            'subject_cd' => ['string', 'max:3', 'digits:3'],
            'how_to_kind'  => ['integer']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
