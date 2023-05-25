@extends('adminlte::page')

@section('title', '授業報告書編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業報告書の変更を行います。</p>

    <x-input.date-picker caption="登録日" id="regist_date" :editData=$editData />

    <x-bs.form-title>講師名</x-bs.form-title>
    {{-- <p class="edit-disp-indent">{{$editData->tname}}</p> --}}
    <p class="edit-disp-indent">CWテスト教師１０１</p>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.card>
        {{-- 個別指導・集団授業の選択 --}}
        <x-bs.form-group>
            <x-input.radio caption="個別指導" id="lesson_type-1" name="lesson_type" value="1" :checked=true :editData=$editData />
            <x-input.radio caption="集団授業" id="lesson_type-2" name="lesson_type" value="2" :editData=$editData />
        </x-bs.form-group>
        {{-- 余白 --}}
        <div class="mb-3"></div>

        {{-- チェンジイベントを取得し、校舎と講師を取得する --}}
        {{-- <x-input.select caption="生徒" id="sidKobetsu" :select2=true onChange="selectChangeGetMulti"
            :mastrData=$student_kobetsu_list :editData=$editData /> --}}
            <x-input.select caption="生徒" id="student_id" :select2=true :editData=$editData>
                <option value="1">CWテスト生徒１</option>
                <option value="2">CWテスト生徒２</option>
                <option value="3">CWテスト生徒３</option>
            </x-input.select>

        {{-- チェンジイベントを取得し、授業日時と生徒名、校舎を取得する --}}
        {{-- hidden 退避用--}}
        <x-input.hidden id="_id" :editData=$editData />
        <x-input.select caption="授業日時" id="lesson_date" :select2=true :editData="$editData">
            <option value="1">2023/05/15 16:00</option>
            <option value="2">2023/05/22 16:00</option>
            <option value="3">2023/05/29 16:00</option>
        </x-input.select>

        <x-input.select caption="時限" id="period_no" :select2=true onChange="selectChangeGetMulti" :editData=$editData>
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
                <td><span v-cloak>久我山</span></td>
            </tr>
            <tr>
                <th class="t-minimum">科目</th>
                <td><span v-cloak>数学</span></td>
            </tr>
        </x-bs.table>

    </x-bs.card>

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