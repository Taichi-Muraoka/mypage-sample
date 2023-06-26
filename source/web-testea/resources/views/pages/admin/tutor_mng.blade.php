@extends('adminlte::page')

@section('title', '講師一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="roomcd" caption="校舎" :select2=true >
                <option value="1">久我山</option>
                <option value="2">西永福</option>
                <option value="3">下高井戸</option>
                <option value="4">駒込</option>
                <option value="5">日吉</option>
                <option value="6">自由が丘</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-bs.form-group name="status_groups">
                <x-bs.form-title>講師ステータス</x-bs.form-title>
                {{-- ステータスチェックボックス --}}
                @for ($i = 0; $i < count($statusGroup); $i++)
                <x-input.checkbox :caption="$statusGroup[$i]"
                        :id="'status_group_' . $statusGroup[$i]"
                        name="status_groups" :value="$statusGroup[$i]" />
                @endfor
            </x-bs.form-group>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="tid" caption="講師ID" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="講師名" :rules=$rules />
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
            <th width="12%">講師ID</th>
            <th width="18%">講師名</th>
            <th>メールアドレス</th>
            <th>講師ステータス</th>
            <th></th>
        </x-slot>

        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.tid}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.email}}</td>
            <td>在籍</td>
            <td>
                <x-button.list-edit href="{{ route('tutor_mng-detail', 101) }}" caption="講師情報" />
                <x-button.list-edit vueHref="'{{ route('tutor_mng-calendar', '') }}/' + item.tid" caption="カレンダー" />
                <x-button.list-dtl vueHref="'{{ route('tutor_mng-weekly_shift', '') }}/' + item.tid" caption="空き時間" />
                <x-button.list-dtl vueHref="'{{ route('tutor_mng-salary', '') }}/' + item.tid" caption="給与明細" />
            </td>
        </tr>

    </x-bs.table>
</x-bs.card-list>

@stop