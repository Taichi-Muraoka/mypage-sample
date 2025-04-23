@extends('adminlte::page')

@section('title', '会員一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false :blank=false />
            @else
            {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false :blank=true />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="grade_cd" caption="学年" :select2=true :mastrData=$gradeList :editData=$editData :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="student_id" caption="生徒ID" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="生徒名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-bs.form-group name="status_groups">
                <x-bs.form-title>会員ステータス</x-bs.form-title>
                {{-- チェックボックス --}}
                @for ($i = 0; $i < count($statusList); $i++)
                <x-input.checkbox :caption="$statusList[$i]->value"
                        :id="'status_group_' . $statusList[$i]->code"
                        name="status_groups" :value="$statusList[$i]->code" />
                @endfor
            </x-bs.form-group>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="enter_term" caption="通塾期間" :select2=true :mastrData=$enterTermList :editData=$editData :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('member_mng-new') }}" :small=true />
        <x-button.submit-exec caption="CSVダウンロード" icon="fas fa-download" />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>生徒ID</th>
            <th>生徒名</th>
            <th>学年</th>
            <th>入会日</th>
            <th>通塾期間</th>
            <th>通塾バッジ数</th>
            <th>会員ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.student_id}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.grade_name}}</td>
            <td>@{{$filters.formatYmd(item.enter_date)}}</td>
            <td>@{{$filters.formatTotalMonth(item.enter_term)}}</td>
            <td>@{{item.badge_count}}</td>
            <td>@{{item.stu_status_name}}</td>
            <td>
                <x-button.list-dtl vueHref="'{{ route('member_mng-detail', '') }}/' + item.student_id" caption="生徒カルテ" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- モーダル(CSV出力確認モーダル) --}}
@include('pages.admin.modal.member_output-modal', ['modal_send_confirm' => true])

@stop