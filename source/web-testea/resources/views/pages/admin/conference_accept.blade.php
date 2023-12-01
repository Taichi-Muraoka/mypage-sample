@extends('adminlte::page')

@section('title', '面談日程受付一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true onChange="selectChangeGetRoom" :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false/>
            @else
            <x-input.select id="campus_cd" caption="校舎" :select2=true onChange="selectChangeGetRoom" :mastrData=$rooms :editData=$editData 
                :select2Search=false emptyValue="-1"/>
            @endcan
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="ステータス" id="status" :select2=true :mastrData=$states :select2Search=false :editData=$editData />
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="生徒名" id="student_id" :select2=true :editData=$editData>
                {{-- vueで動的にプルダウンを作成 --}}
                <option v-for="item in selectGetItem.selectItems" :value="item.id">
                    @{{ item.value }}
                </option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="連絡日 From" id="apply_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="連絡日 To" id="apply_date_to" />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('conference_accept-new') }}" :small=true caption="面談追加登録" />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>連絡日</th>
            <th>生徒名</th>
            <th>校舎</th>
            <th>面談日</th>
            <th>開始時刻</th>
            <th>面談担当者</th>
            <th>ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            {{-- MEMO: 日付フォーマットを指定する --}}
            <td>@{{$filters.formatYmd(item.apply_date)}}</td>
            <td>@{{item.student_name}}</td>
            <td>@{{item.campus_name}}</td>
            <td>@{{$filters.formatYmd(item.conference_date)}}</td>
            <td>@{{item.start_time}}</td>
            <td>@{{item.adm_name}}</td>
            <td>@{{item.status_name}}</td>
            <td>
                {{-- モーダルを開く際のIDを指定する。オブジェクトを渡すのでコロンを付ける --}}
                <x-button.list-dtl :vueDataAttr="['id' => 'item.conference_id']" />
                {{-- 編集 URLとIDを指定。IDはVueで指定される。 --}}
                <x-button.list-edit vueHref="'{{ route('conference_accept-edit', '') }}/' + item.conference_id" caption="日程登録" 
                    {{-- 登録済みの場合は非活性 --}}
                    vueDisabled="item.status == {{ App\Consts\AppConst::CODE_MASTER_5_1 }}"/>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.conference_accept-modal')

@stop