<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 授業報告書 - モデル
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
    protected $table = 'report';

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
        'sid',
        'lesson_date',
        'start_time',
        'tid',
        'lesson_type',
        'roomcd',
        'id',
        'r_minutes',
        'content',
        'homework',
        'teacher_comment',
        'parents_comment',
        'regist_time'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = [
        'lesson_date',
        'start_time',
        'regist_time'
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
            'sid' => ['integer'],
            'lesson_date' => ['date_format:Y-m-d'],
            'start_time' => ['vdTime'],
            'tid' => ['integer'],
            'lesson_type' => ['integer'],
            'roomcd' => ['string', 'max:4'],
            'id' => ['integer'],
            'r_minutes' => ['integer', 'max:999'],
            'content' => ['string', 'max:1000'],
            'homework' => ['string', 'max:1000'],
            'teacher_comment' => ['string', 'max:1000'],
            'parents_comment' => ['string', 'max:1000'],
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
     * 検索 roomcd
     */
    public function scopeSearchRoom($query, $obj)
    {
        $key = 'roomcd';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }
}
