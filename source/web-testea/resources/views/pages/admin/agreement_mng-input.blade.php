@extends('adminlte::page')

@section('title', (request()->routeIs('agreement_mng-edit')) ? '契約編集' : '契約登録')

{{-- 子ページ --}}
@section('child_page', true)

{{-- 四階層目の場合：親ページ（二・三階層目）を指定(URLとタイトル) --}}
@section('parent_page', route('member_mng-detail', $editData['sid']))

@section('parent_page_title', '生徒カルテ')

@section('parent_page2', route('agreement_mng', $editData['sid']))

@section('parent_page_title2', '契約一覧')

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の生徒の契約の{{(request()->routeIs('agreement_mng-edit')) ? '変更' : '登録'}}を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">校舎</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.date-picker caption="開始日" id="start_date" :editData=$editData />
    <x-input.date-picker caption="終了日" id="end_date" :editData=$editData />
    <x-input.text caption="月額" id="monthly" :rules=$rules :editData=$editData/>
    <x-input.select caption="受講コース" id="course" :select2=true>
        <option value="1">個別指導</option>
        <option value="2">集団授業</option>
    </x-input.select>

    <x-bs.card>
        <x-bs.form-title>コース詳細</x-bs.form-title>
        <x-input.text caption="講師名" id="teacher" :rules=$rules :editData=$editData/>

        <x-input.select caption="曜日" id="day" :select2=true>
            <option value="1">月曜</option>
            <option value="2">火曜</option>
            <option value="3">水曜</option>
            <option value="4">木曜</option>
            <option value="5">金曜</option>
            <option value="6">土曜</option>
        </x-input.select>

        <x-input.text caption="開始時刻" id="start_time" :rules=$rules :editData=$editData/>
        <x-input.text caption="授業時間（分）" id="time" :rules=$rules :editData=$editData/>
        <x-input.text caption="回数" id="frequency" :rules=$rules :editData=$editData/>

        <x-input.select caption="教科" id="subject" :select2=true :select2Search=false>
            <option value="1">国語</option>
            <option value="2">数学</option>
            <option value="3">理科</option>
            <option value="4">社会</option>
            <option value="5">英語</option>
        </x-input.select>
    </x-bs.card>

    <x-input.textarea caption="備考" id="remarks" :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="karte_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- 前の階層に戻る --}}
            <x-button.back url="{{route('agreement_mng', $editData['sid'])}}" />

            @if (request()->routeIs('agreement_mng-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>

</x-bs.card>

@stop