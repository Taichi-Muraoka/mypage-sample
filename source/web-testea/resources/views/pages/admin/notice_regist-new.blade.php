@extends('adminlte::page')

@section('title', 'お知らせ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下のお知らせの登録を行います。</p>

    <x-input.select id="template_id" caption="定型文選択" :select2=true onChange="selectChangeGetTemplate"
        :mastrData=$templates :editData=$editData />

    <x-input.text id="title" caption="タイトル" :rules=$rules />

    <x-input.textarea id="text" caption="内容" :rules=$rules />

    <x-input.select caption="模試・イベント情報" id="tmid" :select2=true :editData=$editData
        v-show="selectGetItemTemplate.notice_type == {{ App\Consts\AppConst::CODE_MASTER_14_1 }}">
        {{-- vueで動的にプルダウンを作成 --}}
        <option v-for="item in selectGetItemTemplate.selectItemsTm" :value="item.tmid">
            @{{ item.value }}
        </option>
    </x-input.select>

    <x-input.select caption="模試・イベント情報" id="event_id" :select2=true :editData=$editData
        v-show="selectGetItemTemplate.notice_type == {{ App\Consts\AppConst::CODE_MASTER_14_2 }}">
        {{-- vueで動的にプルダウンを作成 --}}
        <option v-for="item in selectGetItemTemplate.selectItemsEvent" :value="item.event_id">
            @{{ item.value }}
        </option>
    </x-input.select>

    <x-input.select id="destination_type" caption="宛先種別" :select2=true onChange="selectChangeGetMulti">
        <option value='{{ App\Consts\AppConst::CODE_MASTER_15_1 }}'>グループ一斉</option>
        <option value='{{ App\Consts\AppConst::CODE_MASTER_15_2 }}'>個別（生徒）</option>
        <option value='{{ App\Consts\AppConst::CODE_MASTER_15_3 }}'>個別（教師）</option>
    </x-input.select>

    {{-- グループ一斉 --}}
    <x-bs.card vShow="form.destination_type == {{ App\Consts\AppConst::CODE_MASTER_15_1 }}">

        <x-bs.form-title>宛先グループ選択</x-bs.form-title>

        <x-bs.form-group name="notice_groups">
            {{-- 宛先チェックボックス --}}
            @for ($i = 0; $i < count($noticeGroup); $i++)
            <x-input.checkbox :caption="$noticeGroup[$i]->value"
                    :id="'notice_group_' . $noticeGroup[$i]->notice_group_id"
                    name="notice_groups" :value="$noticeGroup[$i]->notice_group_id" />
            @endfor
        </x-bs.form-group>

        <x-input.select id="roomcd_group" caption="教室絞り込み（生徒のみ）" :select2=true>
            <option v-for="item in selectGetItem.rooms" :value="item.code">
                @{{ item.value }}
            </option>
        </x-input.select>
    </x-bs.card>

    {{-- 個別（生徒） --}}
    <x-bs.card vShow="form.destination_type == {{ App\Consts\AppConst::CODE_MASTER_15_2 }}">

        <x-input.select id="roomcd_student" caption="教室" :select2=true onChange="selectChangeGetMulti">
            <option v-for="item in selectGetItem.rooms" :value="item.code">
                @{{ item.value }}
            </option>
        </x-input.select>

        <x-input.select id="sid" caption="宛先生徒名" :select2=true>
            <option v-for="item in selectGetItem.students" :value="item.id">
                @{{ item.value }}
            </option>
        </x-input.select>

    </x-bs.card>

    {{-- 個別（教師） --}}
    <x-bs.card vShow="form.destination_type == {{ App\Consts\AppConst::CODE_MASTER_15_3 }}">

        <x-input.select id="tid" caption="宛先教師名" :select2=true>
            <option v-for="item in selectGetItem.teachers" :value="item.id">
                @{{ item.value }}
            </option>
        </x-input.select>

    </x-bs.card>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-new />
        </div>
    </x-slot>

</x-bs.card>

@stop