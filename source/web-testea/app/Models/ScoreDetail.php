<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 生徒成績詳細情報 - モデル
 */
class ScoreDetail extends Model
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
    protected $table = 'score_details';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'score_datail_id';

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
        'score_id',
        'g_subject_cd',
        'score',
        'full_score',
        'average',
        'deviation_score'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'g_subject_cd' => 'string'
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
            'score_datail_id' => ['integer'],
            'score_id' => ['integer'],
            'g_subject_cd' => ['string', 'max:3', 'digits:3'],
            'score' => ['integer', 'max:9999'],
            'full_score' => ['integer', 'max:9999'],
            'average' => ['integer', 'max:9999'],
            'deviation_score' => ['integer', 'max:9999']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
