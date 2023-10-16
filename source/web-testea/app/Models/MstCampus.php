<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 校舎マスタ - モデル
 */
class MstCampus extends Model
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
    protected $table = 'mst_campuses';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'campus_cd';

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
        'campus_cd',
        'name',
        'short_name',
        'email_campus',
        'tel_campus',
        'disp_order',
        'is_hidden'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'campus_cd' => 'string'
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
            'campus_cd' => ['string', 'max:2', 'digits:2'],
            'name' => ['string', 'max:50'],
            'short_name' => ['string', 'max:10'],
            'email_campus' => ['string', 'email:rfc,filter', 'max:100'],
            'tel_campus' => ['string', 'max:20', 'vdTelephone'],
            'disp_order' => ['integer'],
            'is_hidden' => ['integer']
        ];
        return $_fieldRules;
    }
}
