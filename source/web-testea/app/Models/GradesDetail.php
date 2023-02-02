<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 生徒成績詳細情報 - モデル
 */
class GradesDetail extends Model
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
    protected $table = 'grades_detail';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = [
        'grades_id',
        'grades_seq'
    ];

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
        'grades_id',
        'grades_seq',
        'curriculum_name',
        'curriculumcd',
        'score',
        'previoustime',
        'average'

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
            'grades_id' => ['integer'],
            'grades_seq' => ['integer'],
            'curriculum_name' => ['string', 'max:100'],
            'curriculumcd' => ['string', 'max:3'],
            'score' => ['integer', 'min:0', 'max:999'],
            'previoustime' => ['integer', 'in:0,1,2'],
            'average' => ['integer', 'min:0', 'max:999']

        ];
        return $_fieldRules;
    }
}
