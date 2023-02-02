<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 家庭教師標準詳細（業務支援システム連携データ） - モデル
 */
class ExtHomeTeacherStdDetail extends Model
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
    protected $table = 'ext_home_teacher_std_detail';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = [
        'roomcd',
        'sid',
        'std_seq',
        'std_dtl_seq'
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
        'std_seq',
        'std_dtl_seq',
        'tid',
        'std_minutes',
        'std_count',
        'hour_payment',
        'roundtrip_expenses',
        'rem_count',
        'updtime'
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
            'roomcd' => ['string', 'max:4'],
            'sid' => ['integer', 'min:1', 'max:99999999'],
            'std_seq' => ['integer', 'min:1', 'max:999999999'],
            'std_dtl_seq' => ['integer', 'min:1', 'max:999999999'],
            'tid' => ['integer', 'min:1', 'max:999999'],
            'std_minutes' => ['integer', 'min:0', 'max:999'],
            'std_count' => ['integer', 'min:0', 'max:99'],
            'hour_payment' => ['integer', 'min:-99999999', 'max:99999999'],
            'roundtrip_expenses' => ['integer', 'min:-99999999', 'max:99999999'],
            'rem_count' => ['integer', 'min:0', 'max:99'],

            // CSV取り込み向け
            'updtime_csv' => ['date_format:Y/m/d H:i:s']
        ];
        return $_fieldRules;
    }
}
