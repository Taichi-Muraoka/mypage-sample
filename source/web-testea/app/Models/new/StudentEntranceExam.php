<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 生徒受験情報 - モデル
 */
class StudentEntranceExam extends Model
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
    protected $table = 'student_entrance_exams';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'student_exam_id';

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
        'student_id',
        'school_cd',
        'department_name',
        'priority_no',
        'exam_year',
        'exam_name',
        'exam_date',
        'result',
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
            'student_id' => ['integer'],
            'school_cd' => ['string', 'max:13'],
            'department_name' => ['string', 'max:50'],
            'priority_no' => ['integer'],
            'exam_year' => ['string', 'max:4'],
            'exam_name' => ['string', 'max:50'],
            'exam_date' => ['date_format:Y-m-d'],
            'result' => ['integer'],
            'memo' => ['string', 'max:1000']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
