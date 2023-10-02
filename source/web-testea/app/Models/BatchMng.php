<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * バッチ管理 - モデル
 */
class BatchMng extends Model
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
    protected $table = 'batch_mng';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'batch_id';

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
        'batch_type',
        'start_time',
        'end_time',
        'batch_state',
        'adm_id',
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'date',
        'end_time' => 'date',
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
            'batch_id' => ['integer'],
            'batch_type' => ['integer', 'in:1,2,3,4,5,11,12,13,14,21'],
            'start_time' => ['date_format:Y-m-d H:i:s'],
            'end_time' => ['date_format:Y-m-d H:i:s'],
            'batch_state' => ['integer', 'in:0,1,99'],
            'adm_id' => ['integer'],
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
