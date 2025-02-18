@extends('adminlte::page')
@inject('formatter','App\Libs\CommonDateFormat')

@section('title', '授業報告書編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業報告書の変更を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th>登録日</th>
            <td>{{$report['regist_date']->format('Y/m/d')}}</td>
        </tr>
        <tr>
            <th>講師名</th>
            <td>{{$report['tutor_name']}}</td>
        </tr>
        <tr>
            <th width="20%">授業日・時限</th>
            <td>{{$formatter::formatYmdDay($report['lesson_date'])}} {{$report['period_no']}}限</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>{{$report['campus_name']}}</td>
        </tr>
        <tr>
            <th>コース</th>
            <td>{{$report['course_name']}}</td>
        </tr>
        {{-- 個別指導の場合 --}}
        <tr v-show="{{$report['course_kind']}} == {{ App\Consts\AppConst::CODE_MASTER_42_1 }}">
            <th>生徒</th>
            <td>{{$report['student_name']}}</td>
        </tr>
        {{-- 集団授業の場合 --}}
        <tr v-show="{{$report['course_kind']}} == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}">
            <th>受講生徒名</th>
            <td>
                @foreach ($class_member_names as $class_member_name)
                    {{$class_member_name}}<br>
                @endforeach
            </td>
        </tr>
        <tr>
            <th>教科</th>
            <td>{{$report['subject_name']}}</td>
        </tr>
        <tr>
            <th>今月の目標</th>
            <td>{{$report['monthly_goal']}}</td>
        </tr>
        <tr>
            <th>授業教材１</th>
            <td>
                {{$editData['text_name_L1']}} {{$editData['free_text_name_L1']}}
                {{$editData['text_page_L1']}}
            </td>
        </tr>
        <tr>
            <th>授業単元１</th>
            <td>
                {{$editData['category_name1_L1']}} {{$editData['free_category_name1_L1']}}
                {{$editData['unit_name1_L1']}} {{$editData['free_unit_name1_L1']}}<br>
                {{$editData['category_name2_L1']}} {{$editData['free_category_name2_L1']}}
                {{$editData['unit_name2_L1']}} {{$editData['free_unit_name2_L1']}}<br>
                {{$editData['category_name3_L1']}} {{$editData['free_category_name3_L1']}}
                {{$editData['unit_name3_L1']}} {{$editData['free_unit_name3_L1']}}
            </td>
        </tr>
        <tr>
            <th>授業教材２</th>
            <td>
                {{$editData['text_name_L2']}} {{$editData['free_text_name_L2']}}
                {{$editData['text_page_L2']}}
            </td>
        </tr>
        <tr>
            <th>授業単元２</th>
            <td>
                {{$editData['category_name1_L2']}} {{$editData['free_category_name1_L2']}}
                {{$editData['unit_name1_L2']}} {{$editData['free_unit_name1_L2']}}<br>
                {{$editData['category_name2_L2']}} {{$editData['free_category_name2_L2']}}
                {{$editData['unit_name2_L2']}} {{$editData['free_unit_name2_L2']}}<br>
                {{$editData['category_name3_L2']}} {{$editData['free_category_name3_L2']}}
                {{$editData['unit_name3_L2']}} {{$editData['free_unit_name3_L2']}}
            </td>
        </tr>
        <tr>
            <th>確認テスト内容</th>
            <td>{{$report['test_contents']}}</td>
        </tr>
        <tr>
            <th>確認テスト得点</th>
            <td>
                @if($report['test_score'] != null || $report['test_full_score'] != null)
                {{$report['test_score']}} / {{$report['test_full_score']}} 点
                @endif
            </td>
        </tr>
        <tr>
            <th>宿題達成度</th>
            <td>
                <span v-if="{{intval($report['achievement'])}} != 0">
                    {{intval($report['achievement'])}} %
                </span>
            </td>
        </tr>
        <tr>
            <th>達成・課題点</th>
            {{-- nl2br: 改行 --}}
            <td class="nl2br">{{$report['goodbad_point']}}</td>
        </tr>
        <tr>
            <th>解決策</th>
            <td class="nl2br">{{$report['solution']}}</td>
        </tr>
        <tr>
            <th>その他</th>
            <td class="nl2br">{{$report['others_comment']}}</td>
        </tr>
        <tr>
            <th>宿題教材１</th>
            <td>
                {{$editData['text_name_H1']}} {{$editData['free_text_name_H1']}}
                {{$editData['text_page_H1']}}
            </td>
        </tr>
        <tr>
            <th>宿題単元１</th>
            <td>
                {{$editData['category_name1_H1']}} {{$editData['free_category_name1_H1']}}
                {{$editData['unit_name1_H1']}} {{$editData['free_unit_name1_H1']}}<br>
                {{$editData['category_name2_H1']}} {{$editData['free_category_name2_H1']}}
                {{$editData['unit_name2_H1']}} {{$editData['free_unit_name2_H1']}}<br>
                {{$editData['category_name3_H1']}} {{$editData['free_category_name3_H1']}}
                {{$editData['unit_name3_H1']}} {{$editData['free_unit_name3_H1']}}

            </td>
        </tr>
        <tr>
            <th>宿題教材２</th>
            <td>
                {{$editData['text_name_H2']}} {{$editData['free_text_name_H2']}}
                {{$editData['text_page_H2']}}
            </td>
        </tr>
        <tr>
            <th>宿題単元２</th>
            <td>
                {{$editData['category_name1_H2']}} {{$editData['free_category_name1_H2']}}
                {{$editData['unit_name1_H2']}} {{$editData['free_unit_name1_H2']}}<br>
                {{$editData['category_name2_H2']}} {{$editData['free_category_name2_H2']}}
                {{$editData['unit_name2_H2']}} {{$editData['free_unit_name2_H2']}}<br>
                {{$editData['category_name3_H2']}} {{$editData['free_category_name3_H2']}}
                {{$editData['unit_name3_H2']}} {{$editData['free_unit_name3_H2']}}
            </td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select id="approval_status" caption="承認ステータス" :select2=true :mastrData=$statusList :editData=$editData
        :select2Search=false :blank=false />

    <x-input.textarea caption="管理者コメント" id="admin_comment" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="report_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop
