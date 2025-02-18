<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 欠席申請情報 - モデル
 */
class AbsentApplication extends Model
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
    protected $table = 'absent_applications';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'absent_apply_id';

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
        'schedule_id',
        'student_id',
        'absent_reason',
        'status',
        'apply_date'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'apply_date' => 'date'
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
            'absent_apply_id' => ['integer'],
            'schedule_id' => ['integer'],
            'student_id' => ['integer'],
            'absent_reason' => ['string', 'max:1000'],
            'status' => ['integer'],
            'apply_date' => ['date_format:Y-m-d']
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
        $col = 'mst_campuses.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 ステータス
     */
    public function scopeSearchStatus($query, $obj)
    {
        $key = 'status';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 生徒ID
     */
    public function scopeSearchStudentId($query, $obj)
    {
        $key = 'student_id';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 講師ID
     */
    public function scopeSearchTutorId($query, $obj)
    {
        $key = 'tutor_id';
        $col = 'tutors.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }
}
