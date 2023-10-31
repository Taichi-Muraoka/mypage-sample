<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 授業報告書教材単元情報 - モデル
 */
class ReportUnit extends Model
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
    protected $table = 'report_units';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'report_unit_id';

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
        'report_id',
        'sub_cd',
        'text_cd',
        'free_text_name',
        'text_page',
        'unit_category_cd1',
        'free_category_name1',
        'unit_cd1',
        'free_unit_name1',
        'unit_category_cd2',
        'free_category_name2',
        'unit_cd2',
        'free_unit_name2',
        'unit_category_cd3',
        'free_category_name3',
        'unit_cd3',
        'free_unit_name3',
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'sub_cd' => 'string',
        'text_cd' => 'string',
        'unit_category_cd1' => 'string',
        'unit_cd1' => 'string',
        'unit_category_cd2' => 'string',
        'unit_cd2' => 'string',
        'unit_category_cd3' => 'string',
        'unit_cd3' => 'string',
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
            'report_unit_id' => ['integer'],
            'report_id' => ['integer'],
            'sub_cd' => ['string', 'max:2', 'digits:2'],
            'text_cd' => ['string', 'max:8', 'digits:8'],
            'free_text_name' => ['string', 'max:50'],
            'text_page' => ['integer', 'max:2'],
            'unit_category_cd1' => ['string', 'max:7', 'digits:7'],
            'free_category_name1' => ['string', 'max:50'],
            'unit_cd1' => ['string', 'max:2', 'digits:2'],
            'free_unit_name1' => ['string', 'max:50'],
            'unit_category_cd2' => ['string', 'max:7', 'digits:7'],
            'free_category_name2' => ['string', 'max:50'],
            'unit_cd2' => ['string', 'max:2', 'digits:2'],
            'free_unit_name2' => ['string', 'max:50'],
            'unit_category_cd3' => ['string', 'max:7', 'digits:7'],
            'free_category_name3' => ['string', 'max:50'],
            'unit_cd3' => ['string', 'max:2', 'digits:2'],
            'free_unit_name3' => ['string', 'max:50']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
