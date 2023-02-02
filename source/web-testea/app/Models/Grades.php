<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 生徒成績情報 - モデル
 */
class Grades extends Model
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
    protected $table = 'grades';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = 'grades_id';

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
        'sid',
        'exam_type',
        'exam_id',
        'student_comment',
        'regist_time'
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
            'grades_id' => ['integer'],
            'sid' => ['integer'],
            'exam_type' => ['integer', 'in:1,2'],
            'exam_id' => ['integer'],
            'student_comment' => ['string', 'max:1000'],
            'regist_time' => ['date_format:Y-m-d']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 sid
     */
    public function scopeSearchSid($query, $obj)
    {
        $key = 'sid';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 生徒(sid)に紐づく教室
     */
    public function scopeSearchRoom($query, $obj)
    {
        $key = 'roomcd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // sidで教室で絞り込む(共通処理)
            $this->mdlWhereSidByRoomQuery($query, self::class, $obj[$key]);
        }
    }

    /**
     * 検索 生徒(sid)に紐づく教室（教師向け画面からの検索）
     */
    public function scopeSearchRoomForT($query, $obj)
    {
        $key = 'roomcd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // sidで教室で絞り込む(共通処理・教師用)
            $this->mdlWhereSidByRoomQueryForT($query, self::class, $obj[$key]);
        }
    }
}
