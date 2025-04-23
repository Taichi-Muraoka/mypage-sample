<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 授業単元マスタ - モデル
 */
class MstUnit extends Model
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
    protected $table = 'mst_units';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'unit_id';

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
        'unit_id',
        'unit_category_cd',
        'unit_cd',
        'name'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'unit_category_cd' => 'string',
        'unit_cd' => 'string'
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
            'unit_category_cd' => ['string', 'max:7', 'digits:7'],
            'unit_cd' => ['string', 'max:2', 'digits:2'],
            'name' => ['string', 'max:50']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------
    /**
     * 検索 学年コード
     */
    public function scopeSearchGradeCd($query, $obj)
    {
        $key = 'grade_cd';

        // 授業単元分類マスタから検索する
        $col = 'mst_unit_categories.' . $key;

        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 教材科目コード
     */
    public function scopeSearchTextSubjectCd($query, $obj)
    {
        $key = 't_subject_cd';

        // 授業単元分類マスタから検索する
        $col = 'mst_unit_categories.' . $key;

        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 単元分類コード
     */
    public function scopeSearchUnitCategoryCd($query, $obj)
    {
        $key = 'unit_category_cd';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }
}
