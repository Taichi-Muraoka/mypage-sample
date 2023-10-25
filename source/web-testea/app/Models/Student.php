<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 生徒情報 - モデル
 */
class Student extends Model
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
    protected $table = 'students';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'student_id';

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
        'grade_cd',
        'grade_year',
        'birth_date',
        'school_cd_e',
        'school_cd_j',
        'school_cd_h',
        'is_jukensei',
        'tel_stu',
        'tel_par',
        'email_stu',
        'email_par',
        'login_kind',
        'stu_status',
        'enter_date',
        'leave_date',
        'recess_start_date',
        'recess_end_date',
        'past_enter_term',
        'lead_id',
        'storage_link',
        'memo'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'school_cd_e' => 'string',
        'school_cd_j' => 'string',
        'school_cd_h' => 'string',
        'enter_date' => 'date',
        'leave_date' => 'date',
        'recess_start_date' => 'date',
        'recess_end_date' => 'date',
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
            'student_id' => ['integer'],
            'name' => ['string', 'max:50'],
            'name_kana' => ['string', 'max:50'],
            'grade_cd' => ['integer'],
            'grade_year' => ['string', 'max:4'],
            'birth_date' => ['date_format:Y-m-d'],
            'school_cd_e' => ['string', 'max:13'],
            'school_cd_j' => ['string', 'max:13'],
            'school_cd_h' => ['string', 'max:13'],
            'is_jukensei' => ['integer'],
            'tel_stu' => ['string', 'max:20', 'vdTelephone'],
            'tel_par' => ['string', 'max:20', 'vdTelephone'],
            'email_stu' => ['string', 'email:rfc,filter', 'max:100'],
            'email_par' => ['string', 'email:rfc,filter', 'max:100'],
            'login_kind' => ['integer'],
            'stu_status' => ['integer'],
            'enter_date' => ['date_format:Y-m-d'],
            'leave_date' => ['date_format:Y-m-d'],
            'recess_start_date' => ['date_format:Y-m-d'],
            'recess_end_date' => ['date_format:Y-m-d'],
            'past_enter_term' => ['integer'],
            'lead_id' => ['integer'],
            'storage_link' => ['string', 'max:1000'],
            'memo' => ['string', 'max:1000']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

    /**
     * 検索 name
     */
    public function scopeSearchName($query, $obj)
    {
        $key = 'name';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // nameが他とかぶるので、テーブル名を指定した
            $query->where($this->getTable() . '.' . $key, 'LIKE',  '%' . $obj[$key] . '%');
        }
    }

}
