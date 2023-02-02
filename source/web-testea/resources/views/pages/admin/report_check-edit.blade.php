@extends('adminlte::page')

@section('title', '授業報告書編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業報告書の変更を行います。</p>

    <x-input.date-picker caption="登録日" id="regist_time" :editData=$editData />

    <x-bs.form-title>教師名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->tname}}</p>

    <x-bs.form-group name="lesson_type">
        <x-input.radio caption="個別教室" id="lesson_type-1" name="lesson_type"
            value="{{ App\Consts\AppConst::CODE_MASTER_8_1 }}" :checked=true :editData=$editData />
        <x-input.radio caption="家庭教師" id="lesson_type-2" name="lesson_type"
            value="{{ App\Consts\AppConst::CODE_MASTER_8_2 }}" :editData=$editData />
    </x-bs.form-group>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    {{-- 個別教室 --}}
    <x-bs.card vShow="form.lesson_type == {{ App\Consts\AppConst::CODE_MASTER_8_1 }}">

        {{-- チェンジイベントを取得し、教室と教師を取得する --}}
        <x-input.select caption="生徒" id="sidKobetsu" :select2=true onChange="selectChangeGetMulti"
            :mastrData=$student_kobetsu_list :editData=$editData />

        {{-- チェンジイベントを取得し、授業日時と生徒名、教室を取得する --}}
        {{-- hidden 退避用--}}
        <x-input.hidden id="_id" :editData=$editData />
        <x-input.select caption="授業日時" id="id" :select2=true onChange="selectChangeGetMulti" :editData=$editData>
            {{-- 生徒を選択したら動的にリストを作成する --}}
            <option v-for="item in selectGetItem.selectItems" :value="item.id">
                @{{ item.value }}
            </option>
        </x-input.select>

        {{-- 詳細を表示 --}}
        <x-bs.table :hover=false :vHeader=true class="mb-4">
            <tr>
                <th class="t-minimum">教室</th>
                <td><span v-cloak>@{{selectGetItem.class_name}}</span></td>
            </tr>
        </x-bs.table>

    </x-bs.card>

    {{-- 家庭教師 --}}
    <x-bs.card vShow="form.lesson_type == {{ App\Consts\AppConst::CODE_MASTER_8_2 }}">

        {{-- 登録時 --}}
        <x-input.date-picker caption="授業日" id="lesson_date" :editData=$editData />

        <x-input.time-picker caption="開始時刻" id="start_time" :rules=$rules :editData=$editData />

        <x-input.select caption="生徒名" id="sid" :select2=true :mastrData=$student_list :editData=$editData />

    </x-bs.card>

    <x-input.select caption="授業時間数" id="r_minutes" :select2=true :mastrData=$minutes_list :editData=$editData />

    <x-input.textarea caption="学習内容" id="content" :rules=$rules :editData=$editData />

    <x-input.textarea caption="次回までの宿題" id="homework" :rules=$rules :editData=$editData />

    <x-input.textarea caption="教師よりコメント" id="teacher_comment" :rules=$rules :editData=$editData />

    <x-input.textarea caption="保護者よりコメント" id="parents_comment" :rules=$rules :editData=$editData />
    {{-- hidden --}}
    <x-input.hidden id="report_id" :editData=$editData />
    {{-- hidden --}}
    <x-input.hidden id="tid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop