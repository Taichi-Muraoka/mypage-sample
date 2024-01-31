@extends('adminlte::page')

@section('title', '給与算出一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">対象年月</th>
            <th>確定日</th>
            <th>状態</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmString(item.salary_date)}}</td>
            <td>@{{$filters.formatYmString(item.comfirm_date)}}</td>
            <td><span v-if="item.state == {{ App\Consts\AppConst::CODE_MASTER_24_0 }}"
                    class="text-danger">@{{item.state_name}}</span><span v-else>@{{item.state_name}}</span></td>
            <td>
                <x-button.list-dtl vueHref="'{{ route('salary_calculation-detail', '')}}/' + item.id"/>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop