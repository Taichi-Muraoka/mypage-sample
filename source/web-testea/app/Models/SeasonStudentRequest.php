<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 特別期間講習 生徒連絡情報 - モデル
 */
class SeasonStudentRequest extends Model
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
    protected $table = 'season_student_requests';

    /**
     * テーブルの主キー
     *
     * @var array
     */

    protected $primaryKey = 'season_student_id';

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
        'season_cd',
        'campus_cd',
        'apply_date',
        'comment',
        'regist_status',
        'plan_status'
    ];

    /**
     * 属性のキャスト
     *
     * @var array
     */
    protected $casts = [
        'season_cd' => 'string',
        'campus_cd' => 'string',
        'apply_date' => 'date',
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
            'season_student_id' => ['integer'],
            'student_id' => ['integer'],
            'season_cd' => ['string', 'max:6', 'digits:6'],
            'campus_cd' => ['string', 'max:2', 'digits:2'],
            'apply_date' => ['date_format:Y-m-d'],
            'comment' => ['string', 'max:1000'],
            'regist_status' => ['integer'],
            'plan_status' => ['integer']
        ];
        return $_fieldRules;
    }

    //-------------------------------
    // 検索条件
    //-------------------------------

}
