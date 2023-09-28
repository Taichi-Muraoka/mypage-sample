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
        <tr>
            <td>2023</td>
            <td>久我山</td>
            <td>取込済</td>
            <td><x-button.list-send vueHref="'{{ route('year_schedule_import-import', '')}}/' + '久我山'" caption="取込" /></td>
        </tr>
        <tr>
            <td>2023</td>
            <td>西永服</td>
            <td class="text-danger">取込未</td>
            <td><x-button.list-send vueHref="'{{ route('year_schedule_import-import', '')}}/' + '西永福'" caption="取込" /></td>
        </tr>
        <tr>
            <td>2023</td>
            <td>本郷</td>
            <td class="text-danger">取込未</td>
            <td><x-button.list-send vueHref="'{{ route('year_schedule_import-import', '')}}/' + '本郷'" caption="取込" /></td>
        </tr>
        {{-- <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmdHms(item.start_time)}}</td>
            <td>@{{$filters.formatYmdHms(item.end_time)}}</td>
            <td><span v-if="item.batch_state == {{ App\Consts\AppConst::CODE_MASTER_22_1 }}" class="text-danger">@{{item.batch_state_name}}</span><span v-else>@{{item.batch_state_name}}</span></td>
            <td>@{{item.room_name}}</td>
            <td>@{{item.executor}}</td>
        </tr> --}}

    </x-bs.table>

</x-bs.card-list>

@stop