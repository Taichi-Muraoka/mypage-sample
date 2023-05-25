@extends('pages.common.modal')

@section('modal-body')


{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true>
    {{-- モック用処理 --}}
    <tr>
        <th width="35%">申請日</th>
        <td>2023/05/10</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>コース名</th>
        <td>集団授業</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td>久我山</td>
    </tr>
    <tr>
        <th>授業日時</th>
        <td>2023/05/22 16:00</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>CWテスト教師１０１</td>
    </tr>
    <tr>
        <th>欠席理由</th>
        <td class="nl2br">学校行事のため</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>未対応</td>
    </tr>

    {{-- 本番用処理 --}}
    {{-- <tr>
        <th width="35%">申請日</th>
        <td>@{{item.apply_time|formatYmd}}</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>@{{item.sname}}</td>
    </tr>
    <tr>
        <th>コース名</th>
        <td>個別指導コース</td>
    </tr>
    <tr v-show="item.lesson_type == {{ App\Consts\AppConst::CODE_MASTER_8_1 }}">
        <th>校舎</th>
        <td>@{{item.room_name}}</td>
    </tr>
    <tr>
        <th>授業日時</th>
        <td>@{{item.lesson_date|formatYmd}} @{{item.start_time|formatHm}}</td>
    </tr>
    <tr>
        <th>講師名</th>
        <td>@{{item.tname}}</td>
    </tr>
    <tr>
        <th>欠席理由</th>
        <td class="nl2br">@{{item.absent_reason}}</td>
    </tr>
    <tr>
        <th>ステータス</th>
        <td>@{{item.status}}</td>
    </tr> --}}

</x-bs.table>

@overwrite