<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ブースマスタ - モデル
 */
class MstBooth extends Model
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
    protected $table = 'mst_booths';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'booth_id';

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
        'campus_cd',
        'booth_cd',
        'usage_kind',
        'name',
        'disp_order'
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
            'booth_id' => ['integer'],
            'campus_cd' => ['string', 'max:2', 'digits:2'],
            'booth_cd' => ['string', 'max:3', 'digits:3'],
            'usage_kind' => ['integer'],
            'name' => ['string', 'max:50'],
            'disp_order' => ['integer']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------
    /**
     * 検索 校舎コード
     */
    public function scopeSearchCampusCd($query, $obj)
    {
        $key = 'campus_cd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, $obj[$key]);
        }
    }
    /**
     * 検索 用途種別
     */
    public function scopeSearchUsageKind($query, $obj)
    {
        $key = 'usage_kind';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, $obj[$key]);
        }
    }

}
