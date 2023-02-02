<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 振替連絡 - モデル
 */
class TransferApply extends Model
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
    protected $table = 'transfer_apply';

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
        'id',
        'transfer_date',
        'transfer_time',
        'transfer_reason',
        'state',
        'apply_time'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = [
        'transfer_date',
        'transfer_time',
        'apply_time'
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
            'id' => ['integer'],
            'transfer_date' => ['date_format:Y-m-d'],
            'transfer_time' => ['vdTime'],
            'transfer_reason' => ['string', 'max:1000'],
            'state' => ['integer'],
            'apply_time' => ['date_format:Y-m-d']
        ];
        return $_fieldRules;

    }

    //-------------------------------
    // 検索条件
    //-------------------------------
    /**
     * 検索 state
     */
    public function scopeSearchState($query, $obj)
    {
        $key = 'state';
        $model = self::class;
        $modelObj = new $model();
        $col = $modelObj->getTable() . '.' . $key;

        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

}
