<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 振替依頼情報 - モデル
 */
class TransferApplication extends Model
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
    protected $table = 'transfer_applications';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'transfer_apply_id';

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
        'apply_kind',
        'schedule_id',
        'student_id',
        'tutor_id',
        'transfer_reason',
        'transfer_date',
        'monthly_count',
        'approval_status',
        'confirm_date_id',
        'comment',
        'transfer_schedule_id',
        'transfer_kind',
        'substitute_tutor_id'
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
            'transfer_apply_id' => ['integer'],
            'apply_kind' => ['integer', 'in:1,2'],
            'schedule_id' => ['integer'],
            'student_id' => ['integer'],
            'tutor_id' => ['integer'],
            'transfer_reason' => ['string', 'max:1000'],
            'monthly_count' => ['integer'],
            'approval_status' => ['integer', 'in:0,1,2,3,4'],
            'confirm_date_id' => ['integer'],
            'comment' => ['string', 'max:1000'],
            'transfer_schedule_id' => ['integer'],
            'transfer_kind' => ['integer'],
            'substitute_tutor_id' => ['integer']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
