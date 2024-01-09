<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 生徒成績情報 - モデル
 */
class Score extends Model
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
    protected $table = 'scores';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'score_id';

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
        'exam_type',
        'regular_exam_cd',
        'practice_exam_name',
        'term_cd',
        'grade_cd',
        'exam_date',
        'student_comment',
        'regist_date'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'exam_date' => 'date',
        'regist_date' => 'date'
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
            'score_id' => ['integer'],
            'student_id' => ['integer'],
            'exam_type' => ['integer'],
            'regular_exam_cd' => ['integer'],
            'practice_exam_name' => ['string', 'max:50'],
            'term_cd' => ['integer'],
            'grade_cd' => ['integer'],
            'exam_date' => ['date_format:Y-m-d'],
            'student_comment' => ['string', 'max:1000'],
            'regist_date' => ['date_format:Y-m-d']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 student_id
     */
    public function scopeSearchSid($query, $obj)
    {
        $key = 'student_id';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 生徒(student_id)に紐づく校舎
     */
    public function scopeSearchRoom($query, $obj)
    {
        $key = 'campus_cd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // campus_cdで生徒を絞り込む
            $this->mdlWhereSidByRoomQuery($query, self::class, $obj[$key]);
        }
    }

    /**
     * 検索 学年
     */
    public function scopeSearchGradeCd($query, $obj)
    {
        $key = 'grade_cd';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->whereIn($col, $obj[$key]);
        }
    }

    /**
     * 検索 模試
     */
    public function scopeSearchPracticeExam($query, $obj)
    {
        $key = 'exam_type';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 定期考査コード
     */
    public function scopeSearchRegularExamCd($query, $obj)
    {
        // bladeでidとして使う名前が異なるため、テーブル項目名を$dbKeyで指定した
        $key = 'exam_cd';
        $dbKey = 'regular_exam_cd';

        $col = $this->getTable() . '.' . $dbKey;
        if (isset($obj[$key]) && filled($obj[$key])) {
            // 配列の絞り込みwhereIn
            $query->whereIn($col, $obj[$key]);
        }
    }

    /**
     * 検索 学期コード
     */
    public function scopeSearchTermCd($query, $obj)
    {
        // bladeでidとして使う名前が異なるため、テーブル項目名を$dbKeyで指定した
        $key = 'exam_cd';
        $dbKey = 'term_cd';

        $col = $this->getTable() . '.' . $dbKey;
        if (isset($obj[$key]) && filled($obj[$key])) {
            // 配列の絞り込みwhereIn
            $query->whereIn($col, $obj[$key]);
        }
    }

    /**
     * 検索 対象期間From（試験開始日）
     */
    public function scopeSearchExamDateFrom($query, $obj)
    {
        $key = 'date_from';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('exam_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '>=', $obj[$key]);
        }
    }

    /**
     * 検索 対象期間To（試験開始日）
     */
    public function scopeSearchExamDateTo($query, $obj)
    {
        $key = 'date_to';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('exam_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '<=', $obj[$key]);
        }
    }

    /**
     * 検索 対象期間From（登録日）
     */
    public function scopeSearchRegistDateFrom($query, $obj)
    {
        $key = 'date_from';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('regist_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '>=', $obj[$key]);
        }
    }

    /**
     * 検索 対象期間To（登録日）
     */
    public function scopeSearchRegistDateTo($query, $obj)
    {
        $key = 'date_to';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('regist_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '<=', $obj[$key]);
        }
    }
}
