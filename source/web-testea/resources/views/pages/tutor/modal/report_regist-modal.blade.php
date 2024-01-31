@extends('pages.common.modal')

@section('modal-body')

{{-- テーブル --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true>
    <tr>
        <th>登録日</th>
        <td v-cloak>@{{$filters.formatYmd(item.regist_date)}}</td>
    </tr>
    <tr>
        <th width="35%">授業日・時限</th>
        <td v-cloak>@{{$filters.formatYmdDay(item.lesson_date)}} @{{item.period_no}}限</td>
    </tr>
    <tr>
        <th>校舎</th>
        <td v-cloak>@{{item.campus_name}}</td>
    </tr>
    <tr>
        <th>コース</th>
        <td v-cloak>@{{item.course_name}}</td>
    </tr>
    {{-- 個別指導の場合 --}}
    <tr v-show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }}">
        <th>生徒名</th>
        <td v-cloak>@{{item.student_name}}</td>
    </tr>
    {{-- 集団授業の場合 --}}
    <tr v-show="item.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}">
        <th>受講生徒名</th>
        <td><span v-for="member in item.class_member_name" v-cloak>@{{member}}<br></span></td>
    </tr>
    <tr>
        <th>科目</th>
        <td v-cloak>@{{item.subject_name}}</td>
    </tr>
    <tr>
        <th>今月の目標</th>
        <td v-cloak>@{{item.monthly_goal}}</td>
    </tr>
    <tr>
        <th>授業教材１</th>
        <td><span v-for="lesson_text1 in item.lesson_text1" v-cloak>@{{lesson_text1}}&nbsp;</span></td>
    </tr>
    <tr>
        <th>授業単元１</th>
        <td v-cloak>
            <span v-for="lesson_category1_1 in item.lesson_category1_1" v-cloak>@{{lesson_category1_1}}&nbsp;</span><br>
            <span v-for="lesson_category1_2 in item.lesson_category1_2" v-cloak>@{{lesson_category1_2}}&nbsp;</span><br>
            <span v-for="lesson_category1_3 in item.lesson_category1_3" v-cloak>@{{lesson_category1_3}}&nbsp;</span>
        </td>
    </tr>
    <tr>
        <th>授業教材２</th>
        <td><span v-for="lesson_text2 in item.lesson_text2" v-cloak>@{{lesson_text2}}&nbsp;</span></td>
    </tr>
    <tr>
        <th>授業単元２</th>
        <td v-cloak>
            <span v-for="lesson_category2_1 in item.lesson_category2_1" v-cloak>@{{lesson_category2_1}}&nbsp;</span><br>
            <span v-for="lesson_category2_2 in item.lesson_category2_2" v-cloak>@{{lesson_category2_2}}&nbsp;</span><br>
            <span v-for="lesson_category2_3 in item.lesson_category2_3" v-cloak>@{{lesson_category2_3}}&nbsp;</span>
        </td>
    </tr>
    <tr>
        <th>確認テスト内容</th>
        <td v-cloak>@{{item.test_contents}}</td>
    </tr>
    <tr>
        <th>確認テスト得点</th>
        <td v-cloak>
            <span v-if="item.test_score != null && item.test_full_score != null">@{{item.test_score}}/@{{item.test_full_score}}点</span>
        </td>
    </tr>
    <tr>
        <th>宿題達成度</th>
        <td v-cloak>
            <span v-if="item.achievement != 0">@{{item.achievement}}%</span>
        </td>
    </tr>
    <tr>
        <th>達成・課題点</th>
        {{-- nl2br: 改行 --}}
        <td class="nl2br" v-cloak>@{{item.goodbad_point}}</td>
    </tr>
    <tr>
        <th>解決策</th>
        <td class="nl2br" v-cloak>@{{item.solution}}</td>
    </tr>
    <tr>
        <th>その他</th>
        <td class="nl2br" v-cloak>@{{item.others_comment}}</td>
    </tr>
    <tr>
        <th>宿題教材１</th>
        <td><span v-for="homework_text1 in item.homework_text1" v-cloak>@{{homework_text1}}&nbsp;</span></td>
    </tr>
    <tr>
        <th>宿題単元１</th>
        <td v-cloak>
            <span v-for="homework_category1_1 in item.homework_category1_1" v-cloak>@{{homework_category1_1}}&nbsp;</span><br>
            <span v-for="homework_category1_2 in item.homework_category1_2" v-cloak>@{{homework_category1_2}}&nbsp;</span><br>
            <span v-for="homework_category1_3 in item.homework_category1_3" v-cloak>@{{homework_category1_3}}&nbsp;</span>
        </td>
    </tr>
    <tr>
        <th>宿題教材２</th>
        <td><span v-for="homework_text2 in item.homework_text2" v-cloak>@{{homework_text2}}&nbsp;</span></td>
    </tr>
    <tr>
        <th>宿題単元２</th>
        <td v-cloak>
            <span v-for="homework_category2_1 in item.homework_category2_1" v-cloak>@{{homework_category2_1}}&nbsp;</span><br>
            <span v-for="homework_category2_2 in item.homework_category2_2" v-cloak>@{{homework_category2_2}}&nbsp;</span><br>
            <span v-for="homework_category2_3 in item.homework_category2_3" v-cloak>@{{homework_category2_3}}&nbsp;</span>
        </td>
    </tr>
    <tr>
        <th>承認ステータス</th>
        <td class="nl2br" v-cloak>@{{item.status}}</td>
    </tr>
    <tr>
        <th>管理者コメント</th>
        <td class="nl2br" v-cloak>@{{item.admin_comment}}</td>
    </tr>
</x-bs.table>

@overwrite