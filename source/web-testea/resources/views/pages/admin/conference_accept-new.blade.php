@extends('adminlte::page')

@section('title', '面談追加登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>面談スケジュールの登録を行います。</p>

    @can('roomAdmin')
    {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
    <x-input.select id="campus_cd" caption="校舎" :select2=true onChange="selectChangeGet" :mastrData=$rooms :editData=$editData
        :select2Search=false :blank=false/>
    @else
    <x-input.select id="campus_cd" caption="校舎" :select2=true onChange="selectChangeGet" :mastrData=$rooms :editData=$editData 
        :select2Search=false emptyValue="-1"/>
    @endcan

    <x-input.select caption="ブース" id="booth_cd" :select2=true :editData=$editData :select2Search=false>
        {{-- vueで動的にプルダウンを作成 --}}
        <option v-for="item in selectGetItem.selectLists" :value="item.code">
            @{{ item.value }}
        </option>
    </x-input.select>

    <x-input.date-picker caption="面談日" id="target_date" :editData=$editData />

    <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

    <x-input.select caption="生徒（入会前面談時は未選択）" id="student_id" :select2=true :editData=$editData>
        {{-- vueで動的にプルダウンを作成 --}}
        <option v-for="item in selectGetItem.selectItems" :value="item.id">
            @{{ item.value }}
        </option>
    </x-input.select>

    <x-input.textarea caption="管理者メモ" id="memo" :rules=$rules :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            {{-- idを置換 --}}
            <x-button.back />

            {{-- 登録時 --}}
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop