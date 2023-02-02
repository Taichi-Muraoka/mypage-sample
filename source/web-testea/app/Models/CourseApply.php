<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * コース変更・授業追加申請 - モデル
 */
class CourseApply extends Model
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
    protected $table = 'course_apply';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = 'change_id';

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
        'change_type',
        'changes_text',
        'apply_time',
        'changes_state',
        'comment'
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
            'change_id' => ['integer'],
            'change_type' => ['integer'],
            'changes_text' => ['string', 'max:1000'],
            'changes_state' => ['integer'],
            'sid' => ['integer'],
            'apply_time' => ['date_format:Y-m-d'],
            'comment' => ['string', 'max:1000']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

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
     * 検索 変更状態
     */
    public function scopeSearchChangesState($query, $obj)
    {
        $key = 'changes_state';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, '=', $obj[$key]);
        }
    }
}
