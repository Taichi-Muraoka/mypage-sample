<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * サンプル情報 - モデル
 */
class Sample extends Model
{

    // モデルの共通処理
    use \App\Traits\ModelTrait;

    // 論理削除
    use SoftDeletes;

    // Factoryでテストデータ作成時に付加
    use HasFactory;

    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'samples';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'sample_id';

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
        'sample_title',
        'sample_text',
        'regist_date',
        'sample_state',
        'adm_id',
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'regist_date' => 'date',
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
            'sample_id' => ['integer'],
            'student_id' => ['integer'],
            'sample_title' => ['string', 'max:50'],
            'sample_text' => ['string', 'max:1000'],
            'regist_date' => ['date_format:Y-m-d'],
            'sample_state' => ['integer'],
            'adm_id' => ['integer'],
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------
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
     * 検索 ステータス
     */
    public function scopeSearchSampleStates($query, $obj)
    {
        $key = 'sample_state';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, $obj[$key]);
        }
    }

    /**
     * 検索 タイトル
     */
    public function scopeSearchSampleTitle($query, $obj)
    {
        $key = 'sample_title';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($key, 'LIKE',  '%' . $obj[$key] . '%');
        }
    }
}
