@extends('adminlte::page')

@section('title', '面談日程受付一覧')

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
            <x-input.select caption="ステータス" id="state" :select2=true :editData=$editData>
                <option value="1">未登録</option>
                <option value="2">登録済</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select caption="生徒名" id="student_name" :select2=true :editData=$editData>
                <option value="1">CWテスト生徒１</option>
                <option value="2">CWテスト生徒２</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="連絡日 From" id="conference_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="連絡日 To" id="conference_date_to" />
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
        <tr>
            <td>2023/01/16</td>
            <td>CWテスト生徒１</td>
            <td>久我山</td>
            <td>2023/01/30</td>
            <td>16:00</td>
            <td>久我山教室長</td>
            <td>登録済</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('conference_accept-edit', 1) }}" caption="日程登録" disabled/>
            </td>
        </tr>
        <tr>
            <td>2023/01/17</td>
            <td>CWテスト生徒２</td>
            <td>久我山</td>
            <td></td>
            <td></td>
            <td></td>
            <td>未登録</td>
            <td>
                <x-button.list-dtl />
                <x-button.list-edit href="{{ route('conference_accept-edit', 1) }}" caption="日程登録"/>
            </td>
        </tr>

    </x-bs.table>

</x-bs.card-list>

{{-- 詳細 --}}
@include('pages.admin.modal.conference_accept-modal')

@stop