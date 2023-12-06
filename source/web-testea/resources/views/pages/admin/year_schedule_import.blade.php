@extends('adminlte::page')

@section('title', '年間授業カレンダー情報取込一覧')

@section('content')

{{-- カード --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="30%">年度</th>
            <th width="30%">校舎</th>
            <th width="30%">状態</th>
            <th width="10%"></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.school_year}}</td>
            <td>@{{item.room_name}}</td>
            <td>
                <span v-if="item.import_state == {{ App\Consts\AppConst::CODE_MASTER_20_0 }}"
                    class="text-danger">@{{item.import_state_name}}</span>
                <span v-else>@{{item.import_state_name}}</span>
            </td>
            <td>
                <x-button.list-send vueHref="'{{ route('year_schedule_import-import', '')}}/' + item.id" caption="取込" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop