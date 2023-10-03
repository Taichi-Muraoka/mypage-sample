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
            'exam_type' => ['integer', 'in:1,2,3'],
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
     * 検索 生徒(student_id)に紐づく教室
     */
    public function scopeSearchRoom($query, $obj)
    {
        $key = 'campus_cd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // sidで教室で絞り込む(共通処理)
            $this->mdlWhereSidByRoomQuery($query, self::class, $obj[$key]);
        }
    }

    /**
     * 検索 生徒(student_id)に紐づく教室（講師向け画面からの検索）
     */
    public function scopeSearchRoomForT($query, $obj)
    {
        $key = 'campus_cd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // student_idで教室で絞り込む(共通処理・講師用)
            $this->mdlWhereSidByRoomQueryForT($query, self::class, $obj[$key]);
        }
    }

}
