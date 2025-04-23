<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Consts\AppConst;
use App\Mail\ExtraClassApplyToOffice;
use App\Models\ExtraClassApplication;
use App\Models\MstCampus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

/**
 *  追加授業依頼- コントローラ
 */
class ExtraLessonController extends Controller
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // 生徒に紐づく校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.student.extra_lesson', [
            'rules' => $this->rulesForInput(null),
            'editData' => null,
            'rooms' => $rooms,
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

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            //==========================
            // 追加授業依頼情報の登録
            //==========================
            // 生徒IDを取得
            $account = Auth::user();
            $sid = $account->account_id;

            $extraClass = new ExtraClassApplication;
            $extraClass->student_id = $sid;
            $extraClass->campus_cd = $request['campus_cd'];
            // 状態：未対応
            $extraClass->status = AppConst::CODE_MASTER_1_0;
            $extraClass->request = $request['request'];
            // 依頼日：現在日時をセット
            $extraClass->apply_date = Carbon::now();
            // 保存
            $extraClass->save();

            //==========================
            // 校舎へメール送信
            //==========================
            // 生徒名を取得
            $student_name = $this->mdlGetStudentName($sid);

            // メール本文に記載する情報をセット
            $mail_body = [
                'student_name' => $student_name,
                'request' => $request['request'],
            ];

            // 校舎メールアドレスを取得
            $campus = MstCampus::where('campus_cd', $request['campus_cd'])->first();

            // メール送信
            Mail::to($campus->email_campus)->send(new ExtraClassApplyToOffice($mail_body));
        });

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
     * バリデーションルールを取得(事前に渡す用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {
            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();
        $rules += ExtraClassApplication::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += ExtraClassApplication::fieldRules('request', ['required']);

        return $rules;
    }
}
