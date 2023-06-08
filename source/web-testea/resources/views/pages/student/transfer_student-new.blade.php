@extends('adminlte::page')

@section('title', '授業振替依頼')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>個別指導授業の振替依頼を行います。振替日は第３希望まで指定できます。</p>

    <x-input.select caption="授業日・時限" id="id" :select2=true :editData="$editData">
        <option value="1">2023/01/30 3限</option>
        <option value="2">2023/01/30 4限</option>
        <option value="3">2023/01/31 2限</option>
    </x-input.select>

    <x-bs.form-title>振替希望日</x-bs.form-title>
    {{-- 第１希望日 --}}
    {{-- id="preferred_date1" --}}
    <x-bs.card>
        <x-input.select caption="第１希望日（担当講師の空きコマから選択）" id="preferred_date1" :select2=true :select2Search=false :editData=$editData>
            <option value="1">2023/03/20 4限</option>
            <option value="2">2023/03/20 5限</option>
            <option value="3">2023/03/27 4限</option>
            <option value="4">2023/03/27 5限</option>
            <option value="5">2023/04/03 4限</option>
            <option value="6">2023/04/03 5限</option>
            <option value="7">2023/04/10 4限</option>
            <option value="8">2023/04/10 5限</option>
        </x-input.select>

        <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules />
    </x-bs.card>

    {{-- 第２希望日 --}}
    {{-- id="preferred_date2" --}}
    <x-bs.card>
        <x-input.select caption="第２希望日（担当講師の空きコマから選択）" id="preferred_date2" :select2=true :select2Search=false :editData=$editData>
            <option value="1">2023/03/20 4限</option>
            <option value="2">2023/03/20 5限</option>
            <option value="3">2023/03/27 4限</option>
            <option value="4">2023/03/27 5限</option>
            <option value="5">2023/04/03 4限</option>
            <option value="6">2023/04/03 5限</option>
            <option value="7">2023/04/10 4限</option>
            <option value="8">2023/04/10 5限</option>
        </x-input.select>

        <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules />
    </x-bs.card>

    {{-- 第３希望日 --}}
    {{-- id="preferred_date3" --}}
    <x-bs.card>
        <x-input.date-picker caption="第３希望日（フリー入力）" id="transfer_date3" />

        <x-input.select caption="時限" id="period" :select2=true :select2Search=false :editData=$editData>
            <option value="1">1限</option>
            <option value="2">2限</option>
            <option value="3">3限</option>
            <option value="4">4限</option>
            <option value="5">5限</option>
            <option value="6">6限</option>
            <option value="7">7限</option>
            <option value="8">8限</option>
        </x-input.select>

        <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules />
    </x-bs.card>

    <x-input.textarea caption="振替理由" id="transfer_reason" :rules=$rules />

    {{-- hidden --}}
    <x-input.hidden id="transfer_student_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 登録時 --}}
            <x-button.submit-new />

        </div>
    </x-slot>

</x-bs.card>

@stop