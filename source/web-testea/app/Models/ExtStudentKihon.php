<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 生徒基本情報（業務支援システム連携データ） - モデル
 */
class ExtStudentKihon extends Model
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
    protected $table = 'ext_student_kihon';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = 'sid';

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
        'sid',
        'name',
        'cls',
        'mailaddress1',
        'enter_date',
        'disp_flg',
        'updtime'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = [];

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
            'sid' => ['integer', 'min:1', 'max:99999999'],
            'name' => ['string', 'max:50'],
            // 項目名変更(cls -> cls_cd)
            'cls_cd' => ['string', 'max:2'],
            // emailバリデーションを見直し・string/maxも追加
            // 項目名変更(mailaddress -> mailaddress1)
            'mailaddress1' => ['string', 'email:rfc,filter', 'max:100'],
            'enter_date' => ['date_format:Y-m-d'],
            'disp_flg' => ['integer', 'between:0,1'],

            // CSV取り込み向け
            'enter_date_csv' => ['date_format:Y/m/d'],
            'updtime_csv' => ['date_format:Y/m/d H:i:s']
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
    /**
     * 検索 cls_cd
     */
    public function scopeSearchCls($query, $obj)
    {
        $key = 'cls_cd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // nameが他とかぶるので、テーブル名を指定した
            $query->where($this->getTable() . '.' . $key, '=', $obj[$key]);
        }
    }

    /**
     * 検索 生徒(sid)に紐づく教室
     */
    public function scopeSearchRoom($query, $obj)
    {
        $key = 'roomcd';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // sidで教室で絞り込む(共通処理)
            $this->mdlWhereSidByRoomQuery($query, self::class, $obj[$key]);
        }
    }

    /**
     * 検索 sid
     */
    public function scopeSearchSid($query, $obj)
    {
        $key = 'sid';
        if (isset($obj[$key]) && filled($obj[$key])) {
            // sidが他とかぶるので、テーブル名を指定した
            $query->where($this->getTable() . '.' . $key, '=', $obj[$key]);
        }
    }
}
