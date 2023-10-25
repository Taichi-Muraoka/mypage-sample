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
            <x-input.select id="roomcd" caption="校舎（送信元）" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false />
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            {{-- 本番用 --}}
            {{-- <x-input.select id="destination_type" caption="宛先種別" :select2=true :mastrData=$destination_types :editData=$editData /> --}}

            {{-- モック用 --}}
            <x-input.select id="destination_type" caption="宛先種別" :select2=true :mastrData=$destination_types :editData=$editData
                :select2Search=false >
                <option value="4">個別（保護者メール）</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            {{-- 本番用 --}}
            {{-- <x-input.select id="notice_type" caption="お知らせ種別" :select2=true :editData=$editData :mastrData=$typeList
                :select2Search=false /> --}}

            {{-- モック用 --}}
            <x-input.select id="notice_type" caption="お知らせ種別" :select2=true :editData=$editData 
                :select2Search=false >
                <option value="4">その他</option>
                <option value="5">面談</option>
                <option value="6">特別期間講習</option>
                <option value="7">成績登録</option>
                <option value="8">請求</option>
                <option value="9">給与</option>
                <option value="10">追加請求</option>
            </x-input.select>
        </x-bs.col2>
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
            <th width="15%">お知らせ種別</th>
            <th width="15%">宛先種別</th>
            <th width="15%">送信元</th>
            <th></th>
        </x-slot>

        {{-- モック用 --}}
        <tr>
            <td>2023/06/16</td>
            <td>面談のご案内</td>
            <td>面談</td>
            <td>グルーブ一斉</td>
            <td>本部</td>
            <td>
                <x-button.list-dtl href="{{ route('notice_regist-detail', 1) }}" caption="お知らせ情報"/>
            </td>
        </tr>

        {{-- 本番用 --}}
        {{-- <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.date)}}</td>
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