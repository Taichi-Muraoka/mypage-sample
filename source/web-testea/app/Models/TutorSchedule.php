<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 教師スケジュール - モデル
 */
class TutorSchedule extends Model
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
    protected $table = 'tutor_schedule';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = 'tutor_schedule_id';

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
        'tid',
        'start_date',
        'start_time',
        'end_time',
        'title',
        'roomcd'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = [
        'start_date', 'start_time', 'end_time'
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
            'tutor_schedule_id' => ['integer'],
            'tid' => ['integer'],
            'start_date' => ['date_format:Y-m-d'],
            'start_time' => ['vdTime'],
            'end_time' => ['vdTime'],
            'title' => ['string', 'max:100'],
            'roomcd' => ['string', 'max:4']
        ];
        return $_fieldRules;
    }
}
