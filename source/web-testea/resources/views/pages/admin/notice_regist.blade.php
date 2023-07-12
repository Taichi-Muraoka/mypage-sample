@extends('adminlte::page')

@section('title', 'お知らせ一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="roomcd" caption="校舎（送信元）" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="校舎（送信元）" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            {{-- 本番用 --}}
            {{-- <x-input.select id="destination_type" caption="宛先種別" :select2=true :mastrData=$destination_types :editData=$editData /> --}}

            {{-- モック用 --}}
            <x-input.select id="destination_type" caption="宛先種別" :select2=true :mastrData=$destination_types :editData=$editData >
                <option value="4">個別（保護者メール）</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.text caption="タイトル" id="title" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('notice_regist-new') }}" caption="新規登録" :small=true />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">通知日</th>
            <th>タイトル</th>
            <th width="15%">宛先種別</th>
            <th width="15%">送信元</th>
            <th></th>
        </x-slot>

        {{-- モック用 --}}
        <tr>
            <td>2023/06/16</td>
            <td>面談のご案内</td>
            <td>グルーブ一斉</td>
            <td>本部</td>
            <td>
                <x-button.list-dtl href="{{ route('notice_regist-detail', 1) }}" caption="お知らせ情報"/>
            </td>
        </tr>

        {{-- 本番用 --}}
        {{-- <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.date|formatYmd}}</td>
            <td>@{{item.title}}</td>
            <td>@{{item.type_name}}</td>
            <td>@{{item.room_name}}</td>
            <td>
                <x-button.list-dtl vueHref="'{{ route('notice_regist-detail', '') }}/' + item.id" caption="お知らせ情報" />
            </td>
        </tr> --}}

    </x-bs.table>

</x-bs.card-list>

@stop