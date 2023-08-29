@extends('adminlte::page')

@section('title', (request()->routeIs('surcharge-edit')) ? '追加請求編集' : '追加請求登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>授業給以外の費用について追加請求申請を行います。</p>

    <x-input.select caption="請求種別" id="kinds" :select2=true :editData="$editData">
        <option value="1">研修（本部）</option>
        <option value="2">研修（教室）</option>
        <option value="3">特別交通費</option>
        <option value="4">生徒獲得</option>
        <option value="5">業務依頼（本部）</option>
        <option value="6">業務依頼（教室）</option>
        <option value="7">経費</option>
        <option value="8">その他</option>
    </x-input.select>

    <x-input.select caption="校舎" id="schools" :select2=true :editData=$editData>
        <option value="0">本部</option>
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="3">下高井戸</option>
        <option value="4">駒込</option>
        <option value="5">日吉</option>
        <option value="6">自由が丘</option>
    </x-input.select>

    {{-- 事務作業の場合 --}}
    <div v-show="form.kinds == 1 || form.kinds == 2 || form.kinds == 5 || form.kinds == 6">
        <x-input.date-picker caption="実施日" id="date" />
        <x-input.text caption="開始時刻" id="start_time" :rules=$rules />
        <x-input.text caption="時間（分）" id="time" :rules=$rules />
        <x-input.textarea caption="内容（作業・費目等）" id="text" :rules=$rules />
    </div>

    {{-- その他経費の場合 --}}
    <div v-show="form.kinds == 3 || form.kinds == 4 || form.kinds == 7 || form.kinds == 8">
        <x-input.date-picker caption="実施日" id="date" />
        <x-input.text caption="金額" id="expense" :rules=$rules />
        <x-input.textarea caption="内容（作業・費目等）" id="text" :rules=$rules />
    </div>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('surcharge-edit'))
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