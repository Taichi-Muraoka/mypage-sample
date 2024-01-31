@extends('pages.common.modal')

@section('modal-body')

{{-- 模試 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_0 }}">
    <tr>
        <th width="35%">登録日</th>
        <td>@{{$filters.formatYmd(item.regist_date)}}</td>
    </tr>
    <tr>
        <th>種別</th>
        <td>@{{item.exam_type_name}}</td>
    </tr>
    <tr>
        <th>試験名</th>
        <td>@{{item.practice_exam_name}}</td>
    </tr>
    <tr>
        <th>試験日（開始日）</th>
        <td>@{{$filters.formatYmd(item.exam_date)}}</td>
    </tr>

    <tr>
        <th colspan="2">成績</th>
    </tr>
    <tr>
        <td colspan="2">
            {{-- tableの中にtableを書くと線が出てしまう noborder-only-topを指定した --}}
            <x-bs.table :bordered=false :hover=false :smartPhoneModal=true  class="modal-fix noborder-only-top">
                <x-slot name="thead">
                    <th width="20%">教科</th>
                    <th width="20%">得点</th>
                    <th width="20%">満点</th>
                    <th width="20%">平均点</th>
                    <th width="20%">偏差値</th>
                </x-slot>

                <tr v-for="scoreDetail in item.scoreDetails" v-cloak>
                    <x-bs.td-sp caption="教科">@{{scoreDetail.g_subject_name}}</x-bs.td-sp>
                    <x-bs.td-sp caption="得点">@{{scoreDetail.score}}<span v-show='scoreDetail.score != null'>点</span></x-bs.td-sp>
                    <x-bs.td-sp caption="満点">@{{scoreDetail.full_score}}<span v-show='scoreDetail.full_score != null'>点</span></x-bs.td-sp>
                    <x-bs.td-sp caption="平均点">@{{scoreDetail.average}}<span v-show='scoreDetail.average != null'>点</span></x-bs.td-sp>
                    <x-bs.td-sp caption="偏差値">@{{scoreDetail.deviation_score}}</x-bs.td-sp>
                </tr>
            </x-bs.table>
        </td>
    </tr>

    <tr>
        <th colspan="2">次回に向けての抱負</th>
    </tr>
    {{-- nl2br: 改行 --}}
    <td colspan="2" class="nl2br">@{{item.student_comment}}</td>
</x-bs.table>


{{-- 定期考査 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_1 }}">
    <tr>
        <th width="35%">登録日</th>
        <td>@{{$filters.formatYmd(item.regist_date)}}</td>
    </tr>
    <tr>
        <th>種別</th>
        <td>@{{item.exam_type_name}}</td>
    </tr>
    <tr>
        <th>試験名</th>
        <td>@{{item.regular_exam_name}}</td>
    </tr>
    <tr>
        <th>試験日（開始日）</th>
        <td>@{{$filters.formatYmd(item.exam_date)}}</td>
    </tr>

    <tr>
        <th colspan="2">成績</th>
    </tr>
    <tr>
        <td colspan="2">
            {{-- tableの中にtableを書くと線が出てしまう noborder-only-topを指定した --}}
            <x-bs.table :bordered=false :hover=false :smartPhoneModal=true  class="modal-fix noborder-only-top">
                <x-slot name="thead">
                    <th width="30%">教科</th>
                    <th width="30%">得点</th>
                    <th width="30%">平均点</th>
                </x-slot>

                <tr v-for="scoreDetail in item.scoreDetails" v-cloak>
                    <x-bs.td-sp caption="教科">@{{scoreDetail.g_subject_name}}</x-bs.td-sp>
                    <x-bs.td-sp caption="得点">@{{scoreDetail.score}}<span v-show='scoreDetail.score != null'>点</span></x-bs.td-sp>
                    <x-bs.td-sp caption="平均点">@{{scoreDetail.average}}<span v-show='scoreDetail.average != null'>点</span></x-bs.td-sp>
                </tr>
            </x-bs.table>
        </td>
    </tr>

    <tr>
        <th colspan="2">次回に向けての抱負</th>
    </tr>
    {{-- nl2br: 改行 --}}
    <td colspan="2" class="nl2br">@{{item.student_comment}}</td>
</x-bs.table>


{{-- 評定 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.exam_type == {{ App\Consts\AppConst::CODE_MASTER_43_2 }}">
    <tr>
        <th width="35%">登録日</th>
        <td>@{{$filters.formatYmd(item.regist_date)}}</td>
    </tr>
    <tr>
        <th>種別</th>
        <td>@{{item.exam_type_name}}</td>
    </tr>
    <tr>
        <th>学期</th>
        <td>@{{item.term_name}}</td>
    </tr>

    <tr>
        <th colspan="2">成績</th>
    </tr>
    <tr>
        <td colspan="2">
            {{-- tableの中にtableを書くと線が出てしまう noborder-only-topを指定した --}}
            <x-bs.table :bordered=false :hover=false :smartPhoneModal=true  class="modal-fix noborder-only-top">
                <x-slot name="thead">
                    <th width="50%">教科</th>
                    <th width="50%">評定値</th>
                </x-slot>

                <tr v-for="scoreDetail in item.scoreDetails" v-cloak>
                    <x-bs.td-sp caption="教科">@{{scoreDetail.g_subject_name}}</x-bs.td-sp>
                    <x-bs.td-sp caption="評定値">@{{scoreDetail.score}}</x-bs.td-sp>
                </tr>
            </x-bs.table>
        </td>
    </tr>

    <tr>
        <th colspan="2">次回に向けての抱負</th>
    </tr>
    {{-- nl2br: 改行 --}}
    <td colspan="2" class="nl2br">@{{item.student_comment}}</td>
</x-bs.table>

@overwrite