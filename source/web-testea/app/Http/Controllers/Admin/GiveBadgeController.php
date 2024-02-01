<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Badge;
use App\Models\Student;
use App\Models\AdminUser;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Lang;

/**
 * バッジ付与一覧 - コントローラ
 */
class GiveBadgeController extends Controller
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    //==========================
    // 一覧
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();

        // バッジ種別リストを取得
        $kindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_55);

        return view('pages.admin.give_badge', [
            'rules' => $this->rulesForSearch(null),
            'rooms' => $rooms,
            'studentList' => $studentList,
            'kindList' => $kindList,
            'editData' => null
        ]);
    }

    /**
     * バリデーション(検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForSearch(Request $request)
    {
        // リクエストデータチェック
        // MEMO:認定日のバリデーション用にrulesForSearchに引数$requestが必要
        $validator = Validator::make($request->all(), $this->rulesForSearch($request));
        return $validator->errors();
    }

    /**
     * 検索結果取得(一覧と一覧出力CSV用)
     * 検索結果一覧を表示するとのCSVのダウンロードが同じため共通化
     *
     * @param mixed $form 検索フォーム
     */
    private function getSearchResult($form)
    {
        // クエリ作成
        $query = Badge::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 校舎の絞り込み条件
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 本部管理者の場合検索フォームから取得
            $query->SearchCampusCd($form);
        }

        // 生徒の絞り込み条件
        $query->SearchStudentId($form);

        // バッジ種別の絞り込み条件
        $query->SearchBadgeType($form);

        // 認定日の絞り込み条件
        $query->SearchAuthorizationDateFrom($form);
        $query->SearchAuthorizationDateTo($form);

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        // バッジ一覧取得
        $badgeList = $query
            ->select(
                'badges.badge_id',
                'badges.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'badges.campus_cd',
                // 校舎の名称
                'campus_names.room_name as campus_name',
                'badges.badge_type',
                // コードマスタの名称（バッジ種別）
                'mst_codes.name as kind_name',
                'badges.reason',
                'badges.authorization_date',
                'badges.adm_id',
                // 管理者アカウントの名前
                'admin_users.name as admin_name',
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('badges.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'badges.student_id', '=', 'students.student_id')
            // 管理者名を取得
            ->sdLeftJoin(AdminUser::class, 'badges.adm_id', '=', 'admin_users.adm_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('badges.badge_type', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_55);
            })
            ->orderBy('badges.authorization_date', 'desc')
            ->orderBy('badges.badge_type', 'asc')
            ->orderBy('badges.campus_cd', 'asc');

        return $badgeList;
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array  検索結果
     */
    public function search(Request $request)
    {
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // 検索結果を取得
        $badgeList = $this->getSearchResult($form);

        // ページネータで返却
        return $this->getListAndPaginator($request, $badgeList);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return mixed ルール
     */
    // MEMO:認定日のバリデーション用にrulesForSearchに引数$requestが必要
    private function rulesForSearch(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationCampusList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒ID
        $validationStudentList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $students = $this->mdlGetStudentList();
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック バッジ種別
        $validationKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_55);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 校舎コード
        $rules += Badge::fieldRules('campus_cd', [$validationCampusList]);
        // 生徒ID
        $rules += Badge::fieldRules('student_id', [$validationStudentList]);
        // 用途種別
        $rules += Badge::fieldRules('badge_type', [$validationKindList]);

        // 認定日 項目のバリデーションルールをベースにする
        $ruleAuthorizationDate = Badge::getFieldRule('authorization_date');

        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'authorization_date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        // 日付From・Toのバリデーションの設定
        $rules += ['authorization_date_from' => $ruleAuthorizationDate];
        $rules += ['authorization_date_to' => array_merge($validateFromTo, $ruleAuthorizationDate)];

        return $rules;
    }

    /**
     * 詳細取得（CSV出力の確認モーダル用）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        // ここでの処理は特になし
        return [];
    }

    /**
     * モーダル処理（CSV出力）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        //--------------
        // 一覧出力
        //--------------

        // formを取得
        $form = $request->all();

        // 検索結果を取得
        $badgeList = $this->getSearchResult($form)
            // 結果を取得
            ->get();

        //---------------------
        // CSV出力内容を配列に保持
        //---------------------
        $arrayCsv = [];

        // ヘッダ
        $arrayCsv[] = Lang::get(
            'message.file.give_badge_output.header'
        );

        // 付与バッジ詳細
        foreach ($badgeList as $data) {
            // 一行出力
            $arrayCsv[] = [
                $data->authorization_date->format('Y/m/d'),
                $data->kind_name,
                $data->campus_name,
                $data->student_id,
                $data->student_name,
                $data->admin_name,
                $data->reason
            ];
        }

        //---------------------
        // ファイル名の取得と出力
        //---------------------

        $filename = Lang::get(
            'message.file.give_badge_output.name',
            [
                'outputDate' => date("Ymd")
            ]
        );

        // ファイルダウンロードヘッダーの指定
        $this->fileDownloadHeader($filename, true);

        // CSVを出力する
        $this->outputCsv($arrayCsv);

        return;
    }
}
