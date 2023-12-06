<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 講師空き時間情報 - モデル
 */
class TutorFreePeriod extends Model
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
    protected $table = 'tutor_free_periods';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'free_period_id';

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
        'tutor_id',
        'day_cd',
        'period_no'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [];

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
            'free_period_id' => ['integer'],
            'tutor_id' => ['integer'],
            'day_cd' => ['integer'],
            'period_no' => ['integer']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------
    /**
     * 検索 講師ID
     */
    public function scopeSearchTutorId($query, $obj)
    {
        $key = 'tutor_id';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 曜日コード
     */
    public function scopeSearchDayCd($query, $obj)
    {
        $key = 'day_cd';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 時限
     */
    public function scopeSearchPeriodNo($query, $obj)
    {
        $key = 'period_no';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

}
