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
        'birth_date' => 'date',
        'enter_date' => 'date',
        'leave_date' => 'date',
        'recess_start_date' => 'date',
        'recess_end_date' => 'date',
        'lead_id' => 'string',
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
            'student_id' => ['integer', 'max:9999999999'],
            'name' => ['string', 'max:50'],
            'name_kana' => ['string', 'max:50'],
            'grade_cd' => ['integer'],
            'grade_year' => ['string', 'max:4', 'digits:4'],
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
            'lead_id' => ['string', 'max:9', 'digits_between:1,9'],
            'storage_link' => ['string', 'max:1000'],
            'memo' => ['string', 'max:1000']
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
     * 検索 生徒名
     */
    public function scopeSearchName($query, $obj)
    {
        $key = 'name';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // nameが他とかぶるので、テーブル名を指定した
            $query->where($this->getTable() . '.' . $key, 'LIKE',  '%' . $obj[$key] . '%');
        }
    }

    /**
     * 検索 学年
     */
    public function scopeSearchGradeCd($query, $obj)
    {
        $key = 'grade_cd';
        $col = $this->getTable() . '.' . $key;
        if (isset($obj[$key]) && filled($obj[$key])) {
            $query->where($col, $obj[$key]);
        }
    }

    /**
     * 検索 会員ステータス
     */
    public function scopeSearchStuStatus($query, $obj, $group)
    {
        // $groupは、選択した会員ステータスの配列 [1 => '1',5 => '5'];
        // bladeでnameとして使う名前が異なるため、テーブル項目名を$dbKeyで指定した
        $key = 'status_groups';
        $dbKey = 'stu_status';

        if (isset($obj[$key]) && filled($obj[$key])) {
            // 配列の絞り込みwhereIn
            $query->whereIn($this->getTable() . '.' . $dbKey, $group);
        }
    }

    /**
     * 検索 通塾期間（通塾期間月数算出値をenter_termという別名で扱う）
     */
    public function scopeSearchEnterTerm($query, $obj)
    {
        // 通塾期間の月数範囲を取得するためappconfを利用
        $conf = config('appconf.enter_term');

        $key = 'enter_term';
        $col = $key;

        if (isset($obj[$key]) && filled($obj[$key])) {
            // 選択された期間によってwhere条件の振り分け 月数範囲についてはappconfに記載
            switch ($obj[$key]) {
                case 1:
                    $query->where($col, '<=', $conf[1]['term']);
                    break;
                case 2:
                    $query->whereBetween($col, $conf[2]['term']);
                    break;
                case 3:
                    $query->whereBetween($col, $conf[3]['term']);
                    break;
                case 4:
                    $query->whereBetween($col, $conf[4]['term']);
                    break;
                case 5:
                    $query->whereBetween($col, $conf[5]['term']);
                    break;
                case 6:
                    $query->whereBetween($col, $conf[6]['term']);
                    break;
                case 7:
                    $query->whereBetween($col, $conf[7]['term']);
                    break;
                case 8:
                    $query->whereBetween($col, $conf[8]['term']);
                    break;
                case 9:
                    $query->whereBetween($col, $conf[9]['term']);
                    break;
                case 10:
                    $query->whereBetween($col, $conf[10]['term']);
                    break;
                case 11:
                    $query->whereBetween($col, $conf[11]['term']);
                    break;
                case 12:
                    $query->where($col, '>=', $conf[12]['term']);
                    break;
            }
        }
    }

    /**
     * 検索 生徒(student_id)に紐づく校舎
     */
    public function scopeSearchRoom($query, $obj)
    {
        $key = 'campus_cd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // student_idで校舎で絞り込む(共通処理)
            $this->mdlWhereSidByRoomQuery($query, self::class, $obj[$key]);
        }
    }
}
