<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 学年マスタ - モデル
 */
class MstGrade extends Model
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
    protected $table = 'mst_grades';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'grade_cd';

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
        'grade_cd',
        'school_kind',
        'name',
        'short_name',
        'age'
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
            'grade_cd' => ['integer'],
            'school_kind' => ['integer', 'in:1,2,3,4'],
            'name' => ['string', 'max:30'],
            'short_name' => ['string', 'max:10'],
            'age' => ['integer']
        ];
        return $_fieldRules;
    }
}
