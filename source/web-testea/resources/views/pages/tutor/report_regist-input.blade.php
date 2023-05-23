@extends('adminlte::page')

@section('title', (request()->routeIs('report_regist-edit')) ? '授業報告書編集' : '授業報告書登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業報告書の{{(request()->routeIs('report_regist-edit')) ? '変更' : '登録'}}を行います。</p>

    @if (request()->routeIs('report_regist-edit'))
    {{-- 編集時 --}}
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">授業日時</th>
            <td>{{$editData->lesson_date->format('Y/m/d')}} {{$editData->start_time->format('H:i')}}</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>{{$editData->class_name}}</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>{{$editData->student_name}}</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>
    @else
    {{-- 登録時 --}}
    <x-bs.card>
        {{-- 個別指導・集団授業の選択 --}}
        <x-bs.form-group>
            <x-input.radio caption="個別指導" id="lesson_type-1" name="lesson_type" value="1" :checked=true :editData=$editData />
            <x-input.radio caption="集団授業" id="lesson_type-2" name="lesson_type" value="2" :editData=$editData />
        </x-bs.form-group>
        {{-- 余白 --}}
        <div class="mb-3"></div>

        {{-- チェンジイベントを取得し、校舎と教師を取得する --}}
        <x-input.select vShow="form.lesson_type == 1" caption="生徒" id="sidKobetsu" :select2=true onChange="selectChangeGetMulti"
            :mastrData=$student_kobetsu_list :editData=$editData />

        {{-- チェンジイベントを取得し、授業日時と生徒名、校舎を取得する --}}
        <x-input.select caption="授業日時" id="id" :select2=true onChange="selectChangeGetMulti" :editData=$editData>
            {{-- 生徒を選択したら動的にリストを作成する --}}
            <option v-for="item in selectGetItem.selectItems" :value="item.id">
                @{{ item.value }}
            </option>
        </x-input.select>

        <x-input.select caption="時限" id="time" :select2=true onChange="selectChangeGetMulti" :editData=$editData>
            <option value="1">1限</option>
            <option value="2">2限</option>
            <option value="3">3限</option>
            <option value="4">4限</option>
            <option value="5">5限</option>
            <option value="6">6限</option>
            <option value="7">7限</option>
        </x-input.select>

        {{-- 詳細を表示 --}}
        <x-bs.table :hover=false :vHeader=true class="mb-4">
            <tr>
                <th class="t-minimum">校舎</th>
                <td><span v-cloak>@{{selectGetItem.class_name}}</span></td>
            </tr>
        </x-bs.table>
    </x-bs.card>
    @endif

    <x-input.text caption="今月の目標" id="monthly_goal" :rules=$rules :editData=$editData />

    <x-input.text caption="授業教材１" id="lesson_text1" :rules=$rules :editData=$editData />

    <x-input.text caption="授業単元１" id="lesson_unit1" :rules=$rules :editData=$editData />

    <x-input.text caption="授業教材２" id="lesson_text2" :rules=$rules :editData=$editData />

    <x-input.text caption="授業単元２" id="lesson_unit2" :rules=$rules :editData=$editData />

    <x-input.text caption="確認テスト内容" id="test_contents" :rules=$rules :editData=$editData />

    <x-input.text caption="確認テスト得点" id="test_score" :rules=$rules :editData=$editData />

    <x-input.text caption="確認テスト満点" id="test_full_score" :rules=$rules :editData=$editData />

    <x-input.text caption="宿題達成度" id="achievement" :rules=$rules :editData=$editData />

    <x-input.textarea caption="達成・課題点" id="goodbad_point" :rules=$rules :editData=$editData />

    <x-input.textarea caption="解決策" id="solution" :rules=$rules :editData=$editData />

    <x-input.textarea caption="その他" id="others_comment" :rules=$rules :editData=$editData />

    <x-input.text caption="宿題教材１" id="homework_text1" :rules=$rules :editData=$editData />

    <x-input.text caption="宿題単元１" id="homework_unit1" :rules=$rules :editData=$editData />

    <x-input.text caption="宿題教材２" id="homework_text2" :rules=$rules :editData=$editData />

    <x-input.text caption="宿題単元２" id="homework_unit2" :rules=$rules :editData=$editData />

    @if (request()->routeIs('report_regist-edit'))
    {{-- 編集時 承認ステータス・事務局コメント--}}
    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th>承認ステータス</th>
            <td></td>
        </tr>
        <tr>
            <th>事務局コメント</th>
            {{-- nl2br: 改行 --}}
            <td class="nl2br"></td>
        </tr>
    </x-bs.table>
    @endif

    {{-- hidden --}}
    <x-input.hidden id="report_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('report_regist-edit'))
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