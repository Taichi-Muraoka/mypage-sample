@extends('adminlte::page')

@section('title', '振替連絡')

@section('content')

{{-- カード --}}
<x-bs.card :form=true>

    <p>個別教室授業の振替連絡を行います。</p>

    {{-- チェンジイベントを取得し、教室と教師を取得する --}}
    <x-input.select caption="生徒" id="sid" :select2=true onChange="selectChangeGetMulti" :mastrData=$students
        :editData=$editData />

    {{-- チェンジイベントを取得し、授業日時と生徒名、教科を取得する --}}
    <x-input.select caption="授業日時" id="id" :select2=true onChange="selectChangeGetMulti" :editData=$editData>
        {{-- 生徒を選択したら動的にリストを作成する --}}
        <option v-for="item in selectGetItem.selectItems" :value="item.id">
            @{{ item.value }}
        </option>
    </x-input.select>

    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th class="t-minimum" width="25%">教室</th>
            <td><span v-cloak>@{{selectGetItem.class_name}}</span></td>
        </tr>
    </x-bs.table>

    <x-input.date-picker caption="振替日" id="transfer_date" />

    <x-input.time-picker caption="開始時刻" id="transfer_time" :rules=$rules />

    <x-input.textarea caption="振替理由" id="transfer_reason" :rules=$rules />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-end">
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop