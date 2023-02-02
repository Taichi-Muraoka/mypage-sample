@extends('adminlte::page')

@section('title', '請求情報取込一覧')

@section('content')

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>請求情報</th>
            <th width="30%">状態</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.invoice_date|formatYmString}}分</td>
            <td><span v-if="item.import_state == {{ App\Consts\AppConst::CODE_MASTER_20_0 }}"
                    class="text-danger">@{{item.state_name}}</span><span v-else>@{{item.state_name}}</span></td>
            <td>
                <x-button.list-send vueHref="'{{ route('invoice_import-import', '')}}/' + item.id" caption="取込" />
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop