@extends('adminlte::page')

@section('title', '会員一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            {{-- @can('roomAdmin') --}}
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            {{-- <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan --}}
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
            {{-- <x-input.select id="cls_cd" caption="学年" :select2=true :editData=$editData :mastrData=$classes /> --}}
            <x-input.select id="cls_cd" caption="学年" :select2=true >
                <option value="1">高3</option>
                <option value="2">高2</option>
                <option value="3">高1</option>
                <option value="4">中3</option>
                <option value="5">中2</option>
                <option value="6">中1</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="sid" caption="生徒No" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="生徒名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-bs.form-group name="status_groups">
                <x-bs.form-title>対象生徒</x-bs.form-title>
                {{-- 教科チェックボックス --}}
                @for ($i = 0; $i < count($statusGroup); $i++)
                <x-input.checkbox :caption="$statusGroup[$i]"
                        :id="'status_group_' . $statusGroup[$i]"
                        name="status_groups" :value="$statusGroup[$i]" />
                @endfor
            </x-bs.form-group>
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
            <th width="15%">生徒No</th>
            <th>生徒名</th>
            <th>メールアドレス</th>
            <th width="15%">学年</th>
            <th width="15%">入会日</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.sid}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.mailaddress1}}</td>
            <td>@{{item.cls_name}}</td>
            <td>@{{item.enter_date|formatYmd}}</td>
            <td>
                <x-button.list-dtl vueHref="'{{ route('member_mng-detail', '') }}/' + item.sid" caption="生徒カルテ" />
                {{-- <x-button.list-edit href="{{ route('member_mng-edit', 1) }}" /> --}}
                {{-- <x-button.list-edit vueHref="'{{ route('member_mng-calendar', '') }}/' + item.sid" caption="カレンダー" /> --}}
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

@stop