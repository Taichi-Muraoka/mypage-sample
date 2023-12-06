@extends('adminlte::page')

@section('title', '講師一覧')

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
            <x-bs.form-group name="status_groups">
                <x-bs.form-title>講師ステータス</x-bs.form-title>
                {{-- ステータスチェックボックス --}}
                @for ($i = 1; $i <= count($statusList); $i++)
                <x-input.checkbox :caption="$statusList[$i]->value"
                        :id="'status_group_' . $statusList[$i]->code"
                        name="status_groups" :value="$statusList[$i]->code" />
                @endfor
            </x-bs.form-group>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="tutor_id" caption="講師ID" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="講師名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="grade_cd" caption="学年" :select2=true :mastrData=$gradeList :editData=$editData :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="hourly_base_wage" caption="ベース給" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('tutor_mng-new') }}" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>
        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>講師ID</th>
            <th>講師名</th>
            <th>学年</th>
            <th>ベース給</th>
            <th>講師ステータス</th>
            <th>勤続年数</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.tutor_id}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.grade_name}}</td>
            <td>@{{item.hourly_base_wage}}</td>
            <td>@{{item.status_name}}</td>
            <td>@{{$filters.formatTotalMonth(item.enter_term)}}</td>
            <td>
                <x-button.list-edit vueHref="'{{ route('tutor_mng-detail', '') }}/' + item.tutor_id" caption="講師情報" />
                <x-button.list-edit vueHref="'{{ route('tutor_mng-calendar', '') }}/' + item.tutor_id" caption="カレンダー" />
                <x-button.list-dtl vueHref="'{{ route('tutor_mng-weekly_shift', '') }}/' + item.tutor_id" caption="空き時間" />
                <x-button.list-dtl vueHref="'{{ route('tutor_mng-salary', '') }}/' + item.tutor_id" caption="給与明細" />
            </td>
        </tr>
    </x-bs.table>
</x-bs.card-list>

@stop