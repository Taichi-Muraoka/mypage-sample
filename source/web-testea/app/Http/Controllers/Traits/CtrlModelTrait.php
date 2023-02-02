<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Auth;
use App\Models\ExtGenericMaster;
use App\Models\CodeMaster;
use App\Models\ExtStudentKihon;
use App\Models\ExtRoom;
use App\Models\TutorRelate;
use App\Models\Office;
use App\Models\Account;
use App\Models\ExtSchedule;
use Illuminate\Support\Facades\DB;

/**
 * モデル - コントローラ共通処理
 */
trait CtrlModelTrait
{

    //==========================
    // 関数名を区別するために
    // mdl(モデル)を先頭につける
    //==========================

    //------------------------------
    // プルダウン向けリストの作成
    //------------------------------

    /**
     * 汎用マスタからプルダウンメニューのリストを取得
     * codeclsを指定する
     *
     * @param string $codecls
     * @return array
     */
    protected function mdlMenuFromExtGenericMaster($codecls)
    {
        return  ExtGenericMaster::select('code', 'name1 as value')
            ->where('codecls', $codecls)
            ->orderby('disp_order')
            ->orderby('code')
            ->get()
            ->keyBy('code');
    }

    /**
     * コードマスタからプルダウンメニューのリストを取得
     * data_typeを指定する
     *
     * @param integer $dataType
     * @return array
     */
    protected function mdlMenuFromCodeMaster($dataType)
    {
        return CodeMaster::select('code', 'name as value')
            ->where('data_type', $dataType)
            ->orderby('order_code')
            ->get()
            ->keyBy('code');
    }

    /**
     * 生徒プルダウンメニューのリストを取得
     * 管理者向け（教室管理者の場合は自分の教室のみ）
     * 教室管理者以外は、指定されたroomcdで検索
     *
     * @return array
     */
    protected function mdlGetStudentList($roomcd)
    {
        $query = ExtStudentKihon::query();

        // 生徒の教室の検索(生徒基本情報参照)
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、強制的に教室コードで検索する
            $account = Auth::user();
            $this->mdlWhereSidByRoomQuery($query, ExtStudentKihon::class, $account->roomcd);
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoom([
                'roomcd' => $roomcd
            ]);
        }
        // アカウントテーブルとJOIN（退会生徒非表示対応）
        $query->sdJoin(Account::class, function ($join) {
            $join->on('ext_student_kihon.sid', '=', 'account.account_id')
                ->where('account.account_type', '=', AppConst::CODE_MASTER_7_1);
        });

        // プルダウンリストを取得する
        return $query->select('sid as id', 'name as value')
            ->orderby('id')
            ->get()
            ->keyBy('id');
    }

    /**
     * 生徒プルダウンメニューのリストを取得（お知らせ登録用・生徒ID付き）
     * 管理者向け（教室管理者の場合は自分の教室のみ）
     * 教室管理者以外は、指定されたroomcdで検索
     *
     * @return array
     */
    protected function mdlGetStudentListWithSid($roomcd)
    {
        $query = ExtStudentKihon::query();

        // 生徒の教室の検索(生徒基本情報参照)
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、強制的に教室コードで検索する
            $account = Auth::user();
            $this->mdlWhereSidByRoomQuery($query, ExtStudentKihon::class, $account->roomcd);
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoom([
                'roomcd' => $roomcd
            ]);
        }
        // アカウントテーブルとJOIN（退会生徒非表示対応）
        $query->sdJoin(Account::class, function ($join) {
            $join->on('ext_student_kihon.sid', '=', 'account.account_id')
                ->where('account.account_type', '=', AppConst::CODE_MASTER_7_1);
        });

        // プルダウンリストを取得する
        return $query->select(
            'sid as id',
            DB::raw('CONCAT(sid, "：", name) AS value')
        )
            ->orderby('id')
            ->get()
            ->keyBy('id');
    }

    /**
     * 生徒プルダウンメニューのリストを取得
     * 教師向け
     *
     * @param string $roomcd 教室コード 指定なしの場合null
     * @param string $tid 教師No 
     * @param string $excludeRoomcd 除外する教室コード
     * @return array
     */
    protected function mdlGetStudentListForT($roomcd, $tid, $excludeRoomcd = null)
    {
        $query = ExtStudentKihon::query();

        $query->sdJoin(TutorRelate::class, function ($join) use ($tid, $roomcd, $excludeRoomcd) {
            // sidをjoin
            $join->on('tutor_relate.sid', '=', 'ext_student_kihon.sid')
                // $roomcdの指定があるときのみ、教室コードで絞る
                ->when(isset($roomcd), function ($queryRoomcd) use ($roomcd) {
                    return $queryRoomcd->where('roomcd', $roomcd);
                })
                // $roomcdの指定があるときのみ、教室コードで除外する
                ->when(isset($excludeRoomcd), function ($queryRoomcd) use ($excludeRoomcd) {
                    return $queryRoomcd->where('roomcd', '!=', $excludeRoomcd);
                })
                // 指定の教師の担当生徒のみに絞り込み
                ->where('tid', $tid);
        });

        // プルダウンリストを取得する
        return $query->select('ext_student_kihon.sid as id', 'name as value')
            ->distinct()
            ->orderby('id')
            ->get()
            ->keyBy('id');
    }

    /**
     * 事務局アカウントプルダウンメニューのリストを取得
     * 管理者向け（教室管理者の場合は自分の教室のみ）
     *
     * @return array
     */
    protected function mdlGetOfficeList()
    {
        $query = Office::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、教室コードで絞る
            $account = Auth::user();
            $query->where('roomcd', $account->roomcd);
        }
        // アカウントテーブルとJOIN（削除管理者アカウント非表示対応）
        $query->sdJoin(Account::class, function ($join) {
            $join->on('office.adm_id', '=', 'account.account_id')
                ->where('account.account_type', '=', AppConst::CODE_MASTER_7_3);
        });

        // プルダウンリストを取得する
        return $query->select('adm_id', 'name as value')
            ->orderby('adm_id')
            ->get()
            ->keyBy('adm_id');
    }

    /**
     * 教室プルダウンメニューのリストを取得
     * 権限によってメニューが違う
     *
     * @param boolean $honbu 本部を表示するかどうか
     * @return array
     */
    protected function mdlGetRoomList($honbu = true)
    {
        // コードマスタより教室情報を取得
        $codemasters = CodeMaster::select('gen_item1', 'gen_item2')
            ->where('data_type', AppConst::CODE_MASTER_6)
            ->first();

        $query = ExtGenericMaster::query();
        $query->select('code', 'name2 as value', 'disp_order')
            ->where('codecls', $codemasters->gen_item1)
            ->where('code', '<=', $codemasters->gen_item2);

        // プルダウンに含めない教室コードを除外する
        $query->whereNotIn('code', config('appconf.excluded_roomcd'));

        // ログインユーザ
        $account = Auth::user();

        // 権限によって見れるリストを変更する
        if (AuthEx::isRoomAdmin()) {
            //-------------
            // 教室管理者
            //-------------

            // 教室管理者の場合、自分の管理教室のみ絞り込み
            // なのでここでは本部は絶対に追加されない
            $query->where('code', $account->roomcd);
        } else {

            if (AuthEx::isStudent()) {
                //-------------
                // 生徒の場合
                //-------------

                // 自分の在籍している教室のみ対応する（教室情報とJOIN）
                $query->sdJoin(ExtRoom::class, function ($join) use ($account) {
                    // codeとroomcdをjoin
                    $join->on('ext_room.roomcd', '=', 'code')
                        // 自分のものだけ
                        ->where('sid', $account->account_id);
                });
            } else if (AuthEx::isTutor()) {
                //-------------
                // 教師の場合
                //-------------

                // 自分の受け持ち生徒が在籍している教室のみ（教師関連情報とJOIN）
                $query->sdJoin(TutorRelate::class, function ($join) use ($account) {
                    // codeとroomcdをjoin
                    $join->on('tutor_relate.roomcd', '=', 'code')
                        // 自分のものだけ
                        ->where('tid', $account->account_id);
                });
            }

            // 本部を追加するかどうか
            if ($honbu) {
                // 教室リストをプルダウンで表示する
                $queryHonbu = CodeMaster::select('code', 'name as value', 'order_code as disp_order')
                    ->where('data_type', AppConst::CODE_MASTER_6);

                // 本部管理者の場合、コードマスタより「本部」名称取得
                $query->union($queryHonbu);
            }
        }

        // 教室リストを取得
        $rooms = $query->orderBy('disp_order')
            ->get()->keyBy('code');

        return $rooms;
    }

    /**
     * 抽出したスケジュールより日時のプルダウンメニューのリストを取得
     *
     * @param array $lessons ExtScheduleよりget
     * @return array プルダウンメニュー用日時 Y/m/d H:i
     */
    protected function mdlGetScheduleMasterList($lessons)
    {
        // プルダウンメニューを作る
        $scheduleMasterValue = [];
        $scheduleMasterKeys = [];
        if (count($lessons) > 0) {
            foreach ($lessons as $lesson) {
                $lesson['lesson_datetime'] = $lesson['lesson_date']->format('Y/m/d') . " " . $lesson->start_time->format('H:i');
                $schedule = [
                    'id' => $lesson['id'],
                    'value' => $lesson['lesson_date']->format('Y/m/d') . " " . $lesson->start_time->format('H:i')
                ];
                $schedule = (object) $schedule;
                array_push($scheduleMasterKeys, $lesson['id']);
                array_push($scheduleMasterValue, $schedule);
            }
        }

        $res = array_combine($scheduleMasterKeys, $scheduleMasterValue);

        return $res;
    }

    /**
     * スケジュールIDをもとにスケジュールの詳細を取得する
     * 権限によって制御をかける
     * getDataSelectで使用される想定
     * 教室名と生徒名を返却する。機能のみではなかったのでここに定義
     * 
     * @param int $schedule_id スケジュールID
     */
    protected function mdlGetScheduleDtl($schedule_id)
    {

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // $requestからidを取得し、検索結果を返却する。idはスケジュールID
        $query = ExtSchedule::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else if (AuthEx::isTutor()) {
            // 教師は無し(使用しない)
        } else if (AuthEx::isStudent()) {
            // 生徒は無し(使用しない)
            return;
        }

        $lesson = $query
            ->select(
                'room_name_full',
                'name'
            )
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_schedule.roomcd', '=', 'room_names.code');
            })
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('ext_student_kihon.sid', '=', 'ext_schedule.sid');
            })
            ->where('ext_schedule.id', '=', $schedule_id)
            ->firstOrFail();

        return $lesson;
    }

    //------------------------------
    // join向けリストの作成
    //------------------------------

    /**
     * 教室のJOINクエリを取得
     * 権限共通。leftJoinSubされる想定
     *
     * @return array
     */
    protected function mdlGetRoomQuery()
    {
        // コードマスタより教室情報を取得
        $codemasters = CodeMaster::select('gen_item1', 'gen_item2')
            ->where('data_type', AppConst::CODE_MASTER_6)
            ->first();

        // 教室一覧を取得
        $query = ExtGenericMaster::query();
        $query->select('code', 'name1 as room_name_full', 'name2 as room_name', 'name3 as room_name_symbol', 'disp_order')
            ->where('codecls', $codemasters->gen_item1)
            // JOIN用なのでcodeによる絞り込みはしないとする。
            //->where('code', '<=', $codemasters->gen_item2)
        ;

        // 本部管理者の場合、コードマスタより「本部」名称取得
        $queryHonbu = CodeMaster::select('code', 'name as room_name_full', 'name as room_name', 'name as room_name_symbol', 'order_code as disp_order')
            ->where('data_type', AppConst::CODE_MASTER_6);
        $query->union($queryHonbu);

        return $query;
    }

    //------------------------------
    // whereの条件
    //------------------------------

    /**
     * 指定された教室コードのsidのみを絞り込む
     * 教室管理者の場合、ログインされている教室管理者の教室で絞り込み
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model sidを絞るテーブルのモデルクラス
     */
    protected function mdlWhereSidForRoomAdminQuery($query, $model)
    {
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、強制的に教室コードで検索する
            $account = Auth::user();
            $this->mdlWhereSidByRoomQuery($query, $model, $account->roomcd);
        }
    }

    /**
     * 指定された教室コードのsidのみを絞り込む
     * 教室情報を検索。主に教室管理者の場合、
     * 任意の教室の一覧を取得したい場合のwhereに指定する条件を取得する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model sidを絞るテーブルのモデルクラス
     * @param string $roomcd 教室コード
     */
    protected function mdlWhereSidByRoomQuery($query, $model, $roomcd)
    {

        // 教室情報に存在するかチェックする。existsを使用した
        $query->whereExists(function ($query) use ($model, $roomcd) {

            // 対象テーブル(モデルから取得)
            $modelObj = new $model();

            // テーブル名取得
            $table = $modelObj->getTable();

            // 教室情報テーブル
            $extRoom = (new ExtRoom)->getTable();

            // 1件存在するかチェック
            $query->select(DB::raw(1))
                ->from($extRoom)
                // 生徒基本情報と教室情報のsidを連結
                ->whereRaw($table . '.sid = ' . $extRoom . '.sid')
                // 指定された教室ID
                ->where($extRoom . '.roomcd', $roomcd)
                // delete_dt条件の追加
                ->whereNull($extRoom . '.deleted_at');
        });
    }

    /**
     * 指定された教室コードのsidのみを絞り込む（教師向け画面用）
     * 教師関連情報を検索。
     * 任意の教室の一覧を取得したい場合のwhereに指定する条件を取得する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model sidを絞るテーブルのモデルクラス
     * @param string $roomcd 教室コード
     */
    protected function mdlWhereSidByRoomQueryForT($query, $model, $roomcd = null)
    {

        // ログインユーザ情報取得
        $account = Auth::user();

        // 教室情報に存在するかチェックする。existsを使用した
        $query->whereExists(function ($query) use ($model, $roomcd, $account) {

            // 対象テーブル(モデルから取得)
            $modelObj = new $model();

            // テーブル名取得
            $table = $modelObj->getTable();

            // 教師関連情報テーブル
            $tutorRelate = (new TutorRelate)->getTable();

            // 1件存在するかチェック
            $query->select(DB::raw(1))
                ->from($tutorRelate)
                // 対象テーブルと教師関連情報のsidを連結
                ->whereRaw($table . '.sid = ' . $tutorRelate . '.sid')
                // ログインユーザのID（tid）
                ->where($tutorRelate . '.tid', $account->account_id)
                // 教室が指定された場合のみ絞り込み
                ->when($roomcd, function ($query) use ($tutorRelate, $roomcd) {
                    return $query->where($tutorRelate . '.roomcd', $roomcd);
                })
                // delete_dt条件の追加
                ->whereNull($tutorRelate . '.deleted_at');
        });
    }

    /**
     * 指定された教室コードのtidのみを絞り込む（管理者向け画面用）
     * 教師関連情報を検索。
     * 任意の教室の一覧を取得したい場合のwhereに指定する条件を取得する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $roomcd 教室コード
     */
    protected function mdlWhereTidByRoomQuery($query, $model, $roomcd)
    {

        // 教室情報に存在するかチェックする。existsを使用した
        $query->whereExists(function ($query) use ($model, $roomcd) {

            // 対象テーブル(モデルから取得)
            $modelObj = new $model();

            // テーブル名取得
            $table = $modelObj->getTable();

            // 教師関連情報テーブル
            $tutorRelate = (new TutorRelate)->getTable();

            // 1件存在するかチェック
            $query->select(DB::raw(1))
                ->from($tutorRelate)
                // 対象テーブルと教師関連情報のtidを連結
                ->whereRaw($table . '.tid = ' . $tutorRelate . '.tid')
                // 教室が指定された場合のみ絞り込み
                ->when($roomcd, function ($query) use ($tutorRelate, $roomcd) {
                    return $query->where($tutorRelate . '.roomcd', $roomcd);
                })
                // delete_dt条件の追加
                ->whereNull($tutorRelate . '.deleted_at');
        });
    }

    //------------------------------
    // SQLヘルパー
    //------------------------------

    /**
     * テーブル項目の日付のフォーマット 年月日
     *
     * @param string $col カラム名
     */
    protected function mdlFormatYmd($col)
    {
        return DB::raw("DATE_FORMAT(" . $col . ", '%Y-%m-%d')");
    }
}
