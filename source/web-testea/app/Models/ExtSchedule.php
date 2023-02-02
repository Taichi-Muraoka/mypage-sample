<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * スケジュール情報（業務支援システム連携データ） - モデル
 */
class ExtSchedule extends Model
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
    protected $table = 'ext_schedule';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = 'id';

    /**
     * IDが自動増分されるか
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'id'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = [
        'lesson_date', 'start_time', 'end_time'
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
            'id' => ['integer', 'min:1', 'max:2147483647'],
            'roomcd' => ['string', 'max:4'],
            'sid' => ['integer', 'min:1', 'max:99999999'],
            'lesson_type' => ['string', 'max:1'],
            'symbol' => ['string', 'max:4'],
            'curriculumcd' => ['string', 'max:3'],
            'rglr_minutes' => ['integer', 'min:0', 'max:999'],
            'gmid' => ['integer', 'min:1', 'max:2147483647'],
            'period_no' => ['integer', 'min:1', 'max:99999999'],
            'tmid' => ['integer', 'min:1', 'max:2147483647'],
            'tid' => ['integer', 'min:1', 'max:999999'],
            'lesson_date' => ['date_format:Y-m-d'],
            'start_time' => ['vdTime'],
            'r_minutes' => ['integer', 'min:0', 'max:999'],
            'end_time' => ['vdTime'],
            'pre_tid' => ['integer', 'min:1', 'max:999999'],
            'pre_lesson_date' => ['date_format:Y-m-d'],
            'pre_start_time' => ['vdTime'],
            'pre_r_minutes' => ['integer', 'min:0', 'max:999'],
            'pre_end_time' => ['vdTime'],
            'chg_status_cd' => ['string', 'max:1'],
            'diff_time' => ['integer', 'min:0', 'max:999'],
            'substitute_flg' => ['integer', 'min:0', 'max:1'],
            'atd_status_cd' => ['string', 'max:1'],
            'status_info' => ['string', 'max:80'],
            'create_kind_cd' => ['string', 'max:1'],
            'transefer_kind_cd' => ['string', 'max:1'],
            'trn_lesson_date' => ['date_format:Y-m-d'],
            'trn_start_time' => ['vdTime'],
            'trn_r_minutes' => ['integer', 'min:0', 'max:999'],
            'trn_end_time' => ['vdTime'],

            // CSV取り込み向け
            'lesson_date_csv' => ['date_format:Y/m/d'],
            'start_time_csv' => ['date_format:H:i:s'],
            'end_time_csv' => ['date_format:H:i:s'],
            'pre_lesson_date_csv' => ['date_format:Y/m/d'],
            'pre_start_time_csv' => ['date_format:H:i:s'],
            'pre_end_time_csv' => ['date_format:H:i:s'],
            'trn_lesson_date_csv' => ['date_format:Y/m/d'],
            'trn_start_time_csv' => ['date_format:H:i:s'],
            'trn_end_time_csv' => ['date_format:H:i:s'],
            'updtime_csv' => ['date_format:Y/m/d H:i:s']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 roomcd
     */
    public function scopeSearchRoomcd($query, $obj)
    {
        $key = 'roomcd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($this->getTable() . '.' . $key, $obj[$key]);
        }
    }
}
