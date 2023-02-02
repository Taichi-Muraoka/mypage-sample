<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 個別講習情報明細（業務支援システム連携データ） - モデル
 */
class ExtExtraIndDetail extends Model
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
    protected $table = 'ext_extra_ind_detail';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = [
        'roomcd',
        'sid',
        'i_seq',
        'period_no'
    ];

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
        'roomcd',
        'sid',
        'i_seq',
        'period_no',
        'extra_date',
        'curriculumcd',
        'start_time',
        'r_minutes',
        'end_time',
        'tid',
        'updtime'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = ['extra_date', 'start_time', 'end_time'];

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
            'roomcd' => ['string', 'max:4'],
            'sid' => ['integer', 'min:1', 'max:99999999'],
            'i_seq' => ['integer', 'min:1', 'max:999999999'],
            'period_no' => ['integer', 'min:1', 'max:99999999'],
            'extra_date' => ['date_format:Y-m-d'],
            'curriculumcd' => ['string', 'max:3'],
            'start_time' => ['vdTime'],
            'r_minutes' => ['integer', 'min:0', 'max:999'],
            'end_time' => ['vdTime'],
            'tid' => ['integer', 'min:1', 'max:999999'],

            // CSV取り込み向け
            'extra_date_csv' => ['date_format:Y/m/d'],
            'start_time_csv' => ['date_format:H:i:s'],
            'end_time_csv' => ['date_format:H:i:s'],
            'updtime_csv' => ['date_format:Y/m/d H:i:s']
        ];
        return $_fieldRules;
    }
}
