<?php

namespace App\Http\Controllers\Traits;

use App\Http\Controllers\Traits\CtrlResponseTrait;
use App\Consts\AppConst;
use App\Models\MstSchool;
use App\Models\CodeMaster;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;

/**
 * 学校検索モーダル - 機能共通処理
 */
trait FuncSchoolSearchTrait
{
    // 応答共通処理
    use CtrlResponseTrait;

    /**
     * 検索結果取得
     */
    private function getSchoolList($request)
    {
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearchSchool())->validate();

        // formを取得
        $form = $request->all();

        // クエリ作成
        $query = MstSchool::query();

        // 学校種の絞り込み条件
        $query->SearchSchoolKind($form);

        // 設置区分の絞り込み条件
        $query->SearchEstablishKind($form);

        // 学校コードの絞り込み条件
        $query->SearchSchoolCd($form);

        // 学校名の絞り込み条件
        $query->SearchSchoolName($form);

        // 学校一覧取得
        $schoolList = $query
            ->select(
                'mst_schools.school_cd',
                'mst_schools.school_kind_cd',
                // コードマスタの名称(学校種コード)
                'mst_codes_49.name as school_kind_name',
                'mst_schools.establish_kind',
                // コードマスタの名称(設置区分)
                'mst_codes_50.name as establish_name',
                'mst_schools.name as school_name',
            )
            // コードマスターとJOIN（学校種コード）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_schools.school_kind_cd', '=', 'mst_codes_49.code')
                    ->where('mst_codes_49.data_type', AppConst::CODE_MASTER_49);
            }, 'mst_codes_49')
            // コードマスターとJOIN（設置区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_schools.establish_kind', '=', 'mst_codes_50.code')
                    ->where('mst_codes_50.data_type', AppConst::CODE_MASTER_50);
            }, 'mst_codes_50')
            ->orderby('mst_schools.school_cd');

        return $schoolList;
    }

    /**
     * バリデーションルールを取得(学校検索用)
     *
     * @return array ルール
     */
    private function rulesForSearchSchool()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 学校種
        $validationSchoolKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $schoolKinds = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);
            if (!isset($schoolKinds[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 設置区分
        $validationEstablishKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $establish = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);
            if (!isset($establish[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += MstSchool::fieldRules('school_kind_cd', [$validationSchoolKindList]);
        $rules += MstSchool::fieldRules('establish_kind', [$validationEstablishKindList]);
        // 学校コードまたは学校名のどちらか必須
        // 学校名はid名がテーブル項目名と異なるためルールを継承するかたちで記述した
        $ruleName = MstSchool::getFieldRule('name');
        $rules += ['school_name' =>  array_merge($ruleName, ['required_without_all:school_cd'])];
        $rules += MstSchool::fieldRules('school_cd', ['required_without_all:school_name']);

        return $rules;
    }
}
