<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 授業報告書情報 - モデル
 */
class Report extends Model
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
    protected $table = 'reports';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'report_id';

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
        'tutor_id',
        'schedule_id',
        'campus_cd',
        'course_cd',
        'lesson_date',
        'period_no',
        'student_id',
        'monthly_goal',
        'test_contents',
        'test_score',
        'test_full_score',
        'achievement',
        'goodbad_point',
        'solution',
        'others_comment',
        'approval_status',
        'admin_comment',
        'regist_date'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'campus_cd' => 'string',
        'course_cd' => 'string',
        'lesson_date' => 'date',
        'regist_date' => 'date',
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
            'report_id' => ['integer'],
            'tutor_id' => ['integer'],
            'schedule_id' => ['integer'],
            'campus_cd' => ['string', 'max:2'],
            'course_cd' => ['string', 'max:5', 'digits:5'],
            'lesson_date' => ['date_format:Y-m-d'],
            'period_no' => ['integer', 'min:0', 'max:99'],
            'student_id' => ['integer'],
            'monthly_goal' => ['string', 'max:100'],
            'test_contents' => ['string', 'max:100'],
            'test_score' => ['integer', 'min:0', 'max:999'],
            'test_full_score' => ['integer', 'min:0', 'max:999'],
            'achievement' => ['integer', 'min:0', 'max:999'],
            'goodbad_point' => ['string', 'max:1000'],
            'solution' => ['string', 'max:1000'],
            'others_comment' => ['string', 'max:1000'],
            'approval_status' => ['integer'],
            'admin_comment' => ['string', 'max:1000'],
            'regist_date' => ['date_format:Y-m-d']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------
    /**
     * 検索 校舎コード
     */
    public function scopeSearchCampusCd($query, $obj)
    {
        $key = 'campus_cd';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 学年コード
     */
    public function scopeSearchGradeCd($query, $obj)
    {
        $key = 'grade_cd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // 生徒IDでスケジュールを絞り込む(共通処理)
            $this->mdlWhereScheduleBySidQuery($query, self::class, $obj[$key]);
        }
    }

    /**
     * 検索 生徒ID
     */
    public function scopeSearchSid($query, $obj)
    {
        $key = 'student_id';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // 生徒IDでスケジュールを絞り込む(共通処理)
            $this->mdlWhereScheduleBySidQuery($query, self::class, $obj[$key]);
        }
    }

    /**
     * 検索 講師ID
     */
    public function scopeSearchTid($query, $obj)
    {
        $key = 'tutor_id';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 コースコード
     */
    public function scopeSearchCourseCd($query, $obj)
    {
        $key = 'course_cd';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 承認ステータス
     */
    public function scopeSearchApprovalStatus($query, $obj)
    {
        $key = 'approval_status';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 授業実施日From
     */
    public function scopeSearchLessonDateFrom($query, $obj)
    {
        $key = 'lesson_date_from';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('lesson_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '>=', $obj[$key]);
        }
    }

    /**
     * 検索 授業実施日To
     */
    public function scopeSearchLessonDateTo($query, $obj)
    {
        $key = 'lesson_date_to';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('lesson_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '<=', $obj[$key]);
        }
    }

}
