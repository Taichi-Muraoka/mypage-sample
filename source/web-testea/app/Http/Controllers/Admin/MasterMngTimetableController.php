<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ExtStudentKihon;
use App\Models\ExtSchedule;
use App\Models\TransferApply;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\MstTimetable;
use App\Models\CodeMaster;
use App\Models\ExtRirekisho;
use App\Models\Notice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Models\NoticeDestination;
use Carbon\Carbon;
use App\Libs\AuthEx;
use App\Http\Controllers\Traits\FuncTransferTrait;

/**
 * 時間割マスタ管理 - コントローラ
 */
class MasterMngTimetableController extends Controller
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

        // 時間割区分を取得
        $timetableKind = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_37);

        return view('pages.admin.master_mng_timetable', [
            'rules' => $this->rulesForSearch(null),
            'rooms' => $rooms,
            'timetablekind' => $timetableKind,
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
        $validator = Validator::make($request->all(), $this->rulesForSearch());
        return $validator->errors();
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = MstTimetable::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 校舎の絞り込み条件
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 本部管理者の場合検索フォームから取得
            $query->SearchCampusCd($form);
        }

        // 時間割区分の絞り込み条件
        $query->SearchTimetableKind($form);

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        // データを取得
        $mstTimetable = $query
            ->select(
                'mst_timetables.timetable_id as id',
                'mst_timetables.timetable_kind',
                // コードマスタの名称(時間割種別)
                'mst_codes.name as kind_name',
                'mst_timetables.period_no',
                'mst_timetables.start_time',
                'mst_timetables.end_time',
                'mst_timetables.campus_cd',
                // 校舎名
                'campus_names.room_name as campus_name'
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('mst_timetables.campus_cd', '=', 'campus_names.code');
            })
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_timetables.timetable_kind', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_37);
            })
            ->orderby('campus_cd')->orderby('period_no')->orderby('timetable_kind');

        // ページネータで返却
        return $this->getListAndPaginator($request, $mstTimetable);
        // return $this->getListAndPaginatorMock();
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
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

        // 独自バリデーション: リストのチェック 時間割区分
        $validationKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_37);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 校舎コード
        $rules += MstTimetable::fieldRules('campus_cd', [$validationCampusList]);
        // 時間割区分
        $rules += MstTimetable::fieldRules('timetable_kind', [$validationKindList]);

        return $rules;
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 時間割区分を取得
        $timetableKind = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_37);

        // 時限を取得 コードマスタにないのでappconfに定義しています。
        $periodNo = config('appconf.period_no');

        return view('pages.admin.master_mng_timetable-input', [
            'rules' => $this->rulesForSearch(null),
            'rooms' => $rooms,
            'timetablekind' => $timetableKind,
            'periodNo' => $periodNo,
            'editData' => null
        ]);
    }

    /**
     * 登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function create(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        $form = $request->only(
            // 教室管理者の場合の校舎コードのチェックはバリデーション(validationRoomList)で行っている
            'campus_cd',
            'timetable_kind',
            'period_no',
            'start_time',
            'end_time'
        );

        $mstTimetable = new MstTimetable;

        // 登録(ガードは不要)
        $mstTimetable->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($timetableId)
    {
        // IDのバリデーション
        $this->validateIds($timetableId);

        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 時間割区分を取得
        $timetableKind = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_37);

        // 時限を取得 コードマスタにないのでappconfに定義しています。
        $periodNo = config('appconf.period_no');

        // クエリを作成(PKでユニークに取る)
        $mstTimetable = MstTimetable::where('timetable_id', $timetableId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return view('pages.admin.master_mng_timetable-input', [
            'rules' => $this->rulesForInput(null),
            'rooms'=> $rooms,
            'timetablekind' => $timetableKind,
            'periodNo' => $periodNo,
            'editData' => $mstTimetable
        ]);
    }

    /**
     * 編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function update(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            // 教室管理者の場合の校舎コードのチェックはバリデーション(validationRoomList)で行っている
            'campus_cd',
            'period_no',
            'start_time',
            'end_time',
            'timetable_kind'
        );

        // 対象データを取得(IDでユニークに取る)
        $mstTimetable = MstTimetable::where('timetable_id', $request['timetable_id'])
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $mstTimetable->fill($form)->save();

        return;
    }

    /**
     * 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function delete(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'timetable_id');

        // Formを取得
        $form = $request->all();

        // 対象データを取得(IDでユニークに取る)
        $mstTimetable = MstTimetable::where('timetable_id', $form['timetable_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $mstTimetable->forceDelete();

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInput(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInput($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 時間割区分
        $validationKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_37);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 重複チェック
        $validationKey = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // 対象データを取得(UNIQUEキーで取得)
            $mstTimetable = MstTimetable::where('campus_cd', $request['campus_cd'])
                ->where('timetable_kind', $request['timetable_kind'])->where('period_no', $request['period_no']);

            // 変更時は自分のキー以外を検索
            if (filled($request['timetable_id'])) {
                $mstTimetable->where('timetable_id', '!=', $request['timetable_id']);
            }

            $exists = $mstTimetable->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += MstTimetable::fieldRules('campus_cd', ['required', $validationRoomList, $validationKey]);
        $rules += MstTimetable::fieldRules('period_no', ['required', $validationKey]);
        $rules += MstTimetable::fieldRules('timetable_kind', ['required', $validationKindList, $validationKey]);
        $rules += MstTimetable::fieldRules('start_time', ['required']);
        $rules += MstTimetable::fieldRules('end_time', ['required']);

        return $rules;
    }
}
