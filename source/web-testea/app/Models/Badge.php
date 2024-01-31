<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * バッジ付与情報 - モデル
 */
class Badge extends Model
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
    protected $table = 'badges';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'badge_id';

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
        'student_id',
        'campus_cd',
        'badge_type',
        'reason',
        'authorization_date',
        'adm_id'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'campus_cd' => 'string',
        'authorization_date' => 'date'
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
            'badge_id' => ['integer'],
            'student_id' => ['integer'],
            'campus_cd' => ['string', 'max:2', 'digits:2'],
            'badge_type' => ['integer'],
            'reason' => ['string', 'max:30'],
            'authorization_date' => ['date_format:Y-m-d'],
            'adm_id' => ['integer']
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
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 生徒ID
     */
    public function scopeSearchStudentId($query, $obj)
    {
        $key = 'student_id';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 バッジ種別
     */
    public function scopeSearchBadgeType($query, $obj)
    {
        $key = 'badge_type';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, $obj[$key]);
        }
    }

    /**
     * 検索 認定日From
     */
    public function scopeSearchAuthorizationDateFrom($query, $obj)
    {
        $key = 'authorization_date_from';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('authorization_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '>=', $obj[$key]);
        }
    }

    /**
     * 検索 認定日To
     */
    public function scopeSearchAuthorizationDateTo($query, $obj)
    {
        $key = 'authorization_date_to';
        // Ymdに変換して検索する
        $col = $this->mdlFormatYmd('authorization_date');
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, '<=', $obj[$key]);
        }
    }
}
