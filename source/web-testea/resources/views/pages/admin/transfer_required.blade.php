@extends('adminlte::page')

@section('title', '要振替授業一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false :blank=false/>
            @else
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData :select2Search=false/>
            @endcan
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="student_id" caption="生徒名" :select2=true :mastrData=$studentList :editData=$editData/>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="tutor_id" caption="講師名" :select2=true :mastrData=$tutorList :editData=$editData/>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 From" id="target_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="授業日 To" id="target_date_to" />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>校舎</th>
            <th>授業日・時限</th>
            <th>コース</th>
            <th>生徒名</th>
            <th>講師名</th>
            <th>教科</th>
            <th>出欠ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.room_name}}</td>
            <td>@{{$filters.formatYmdDay(item.target_date)}} @{{item.period_no}}限</td>
            <td>@{{item.course_name}}</td>
            <td>@{{item.student_name}}</td>
            <td>@{{item.tutor_name}}</td>
            <td>@{{item.subject_name}}</td>
            <td>@{{item.status_name}}</td>
            <td>
                <x-button.list-edit vueHref="'{{ route('transfer_check-required', '') }}/' + item.id"
                caption="振替情報登録" vueDisabled="item.absent_status != {{ App\Consts\AppConst::CODE_MASTER_35_3 }}" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

@stop