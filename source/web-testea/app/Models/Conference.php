<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 面談連絡情報 - モデル
 */
class Conference extends Model
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
    protected $table = 'conferences';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'conference_id';

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
        'campus_cd',
        'comment',
        'status',
        'apply_date',
        'conference_date',
        'start_time',
        'end_time',
        'conference_schedule_id'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
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
            'conference_id' => ['integer'],
            'student_id' => ['integer'],
            'campus_cd' => ['string', 'max:2'],
            'comment' => ['string', 'max:1000'],
            'status' => ['integer', 'in:0,1'],
            'apply_date' => ['date_format:Y-m-d'],
            'conference_date' => ['date_format:Y-m-d'],
            'start_time' => ['vdTime'],
            'end_time' => ['vdTime'],
            'conference_schedule_id' => ['integer']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
