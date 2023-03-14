@extends('adminlte::page')

@section('title', '授業報告書編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業報告書の変更を行います。</p>

    <x-input.date-picker caption="登録日" id="regist_time" :editData=$editData />

    <x-bs.form-title>講師名</x-bs.form-title>
    <p class="edit-disp-indent">{{$editData->tname}}</p>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.card>
        {{-- チェンジイベントを取得し、校舎と講師を取得する --}}
        <x-input.select caption="生徒" id="sidKobetsu" :select2=true onChange="selectChangeGetMulti"
            :mastrData=$student_kobetsu_list :editData=$editData />

        {{-- チェンジイベントを取得し、授業日時と生徒名、校舎を取得する --}}
        {{-- hidden 退避用--}}
        <x-input.hidden id="_id" :editData=$editData />
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

    <x-input.textarea caption="学習内容" id="content" :rules=$rules :editData=$editData />

    <x-input.textarea caption="次回までの宿題" id="homework" :rules=$rules :editData=$editData />

    <x-input.textarea caption="講師よりコメント" id="teacher_comment" :rules=$rules :editData=$editData />

    <x-input.select caption="承認ステータス" id="status" :select2=true :editData=$editData>
        <option value="1">承認待ち</option>
        <option value="2">承認</option>
        <option value="3">差戻し</option>
    </x-input.select>

    <x-input.textarea caption="事務局コメント" id="admin_comment" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="report_id" :editData=$editData />
    {{-- hidden --}}
    <x-input.hidden id="tid" :editData=$editData />

    <x-bs.callout title="登録の際の注意事項" type="warning">
        承認ステータスを「承認」として更新ボタンを押下すると、 生徒に授業報告書が開示されます。
    </x-bs.callout>

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