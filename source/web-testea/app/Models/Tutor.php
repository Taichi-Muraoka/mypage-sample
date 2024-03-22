<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 講師情報 - モデル
 */
class Tutor extends Model
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
    protected $table = 'tutors';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'tutor_id';

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
        'name',
        'name_kana',
        'tel',
        'email',
        'address',
        'birth_date',
        'gender_cd',
        'grade_cd',
        'grade_year',
        'school_cd_j',
        'school_cd_h',
        'school_cd_u',
        'hourly_base_wage',
        'tutor_status',
        'enter_date',
        'leave_date',
        'memo'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'birth_date' => 'date',
        'grade_year' => 'string',
        'school_cd_j' => 'string',
        'school_cd_h' => 'string',
        'school_cd_u' => 'string',
        'enter_date' => 'date',
        'leave_date' => 'date',
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
            'tutor_id' => ['integer', 'min:0', 'max:9999999999'],
            'name' => ['string', 'max:50'],
            'name_kana' => ['string', 'max:50'],
            'tel' => ['string', 'max:20', 'vdTelephone'],
            'email' => ['string', 'email:rfc,filter', 'max:100'],
            'address' => ['string', 'max:100'],
            'birth_date' => ['date_format:Y-m-d'],
            'gender_cd' => ['integer'],
            'grade_cd' => ['integer'],
            'grade_year' => ['string', 'max:4', 'digits:4'],
            'school_cd_j' => ['string', 'max:13', 'digits:13'],
            'school_cd_h' => ['string', 'max:13', 'digits:13'],
            'school_cd_u' => ['string', 'max:13', 'digits:13'],
            'hourly_base_wage' => ['integer', 'min:0', 'max:9999'],
            'tutor_status' => ['integer'],
            'enter_date' => ['date_format:Y-m-d'],
            'leave_date' => ['date_format:Y-m-d'],
            'memo' => ['string', 'max:1000']
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
     * 検索 講師名
     */
    public function scopeSearchName($query, $obj)
    {
        $key = 'name';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($this->getTable() . '.' . $key, 'LIKE',  '%' . $obj[$key] . '%');
        }
    }

    /**
     * 検索 学年
     */
    public function scopeSearchGradeCd($query, $obj)
    {
        $key = 'grade_cd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($this->getTable() . '.' . $key,  $obj[$key]);
        }
    }

    /**
     * 検索 ベース給
     */
    public function scopeSearchHourlyBaseWage($query, $obj)
    {
        $key = 'hourly_base_wage';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 講師ステータス
     */
    public function scopeSearchTutorStatus($query, $obj, $group)
    {
        // $groupは、選択したステータスの配列 [1 => '1',5 => '5'];
        // bladeでnameとして使う名前が異なるため、テーブル項目名を$dbKeyで指定した
        $key = 'status_groups';
        $dbKey = 'tutor_status';

        if (isset($obj[$key]) && filled($obj[$key])) {
            // 配列の絞り込みwhereIn
            $query->whereIn($this->getTable() . '.' . $dbKey, $group);
        }
    }

    /**
     * 検索 講師の所属校舎
     */
    public function scopeSearchRoom($query, $obj)
    {
        $key = 'campus_cd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // 指定された校舎コードで講師IDを絞り込む
            $this->mdlWhereTidByRoomQuery($query, self::class, $obj[$key]);
        }
    }
}
