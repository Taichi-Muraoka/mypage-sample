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
            <th width="35%">授業日・時限</th>
            {{-- <td>{{$editData->lesson_date->format('Y/m/d')}} {{$editData->start_time->format('H:i')}}</td> --}}
            <td>2023/05/15 4限</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
        <tr>
            <th>科目</th>
            <td>数学</td>
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
        {{-- <x-input.select caption="授業日時" id="id" :select2=true onChange="selectChangeGetMulti" :editData=$editData> --}}
            {{-- 生徒を選択したら動的にリストを作成する --}}
            {{-- <option v-for="item in selectGetItem.selectItems" :value="item.id"> --}}
               {{-- @{{ item.value }} --}}
            {{-- </option> --}}
        {{-- </x-input.select> --}}
        <x-input.select caption="授業日・時限" id="id" :select2=true  :editData=$editData>
            <option value="1">2023/04/24 5限</option>
            <option value="2">2023/04/17 5限</option>
            <option value="3">2023/04/10 5限</option>
            <option value="4">2023/04/03 5限</option>
        </x-input.select>

        {{-- 詳細を表示 --}}
        <x-bs.table vShow="form.id > 0" :hover=false :vHeader=true class="mb-4">
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
    @endif

    <x-input.text caption="今月の目標" id="monthly_goal"/>

    <x-bs.card>
        <x-bs.form-title>授業内容</x-bs.form-title>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材１" id="lesson_text1" :select2=true>
                    <option value="1">数学ドリル基本</option>
                    <option value="2">数学ドリル発展</option>
                    <option value="3">数学ドリル演習</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="lesson_page1"/>
            </x-bs.col2>
        </x-bs.row>

        <x-input.select caption="単元１" id="lesson_unit1" :select2=true>
            <option value="1">正負の数</option>
            <option value="2">比例</option>
            <option value="3">連立方程式</option>
        </x-input.select>

        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材２" id="lesson_text2" :select2=true>
                    <option value="1">数学ドリル基本</option>
                    <option value="2">数学ドリル発展</option>
                    <option value="3">数学ドリル演習</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="lesson_page2"/>
            </x-bs.col2>
        </x-bs.row>

        <x-input.select caption="単元２" id="lesson_unit2" :select2=true>
            <option value="1">正負の数</option>
            <option value="2">比例</option>
            <option value="3">連立方程式</option>
        </x-input.select>
    </x-bs.card>

    <x-bs.card>
    <x-bs.form-title>確認テスト</x-bs.form-title>
    <x-input.text caption="内容" id="test_contents"/>
    <x-bs.row>
        <x-bs.col3>
            <x-input.text caption="得点" id="test_score"/>
        </x-bs.col3>
            <x-bs.form-title></x-bs.form-title>
            <p class="edit-disp-indent">／　</p>
        <x-bs.col3>
            <x-input.text caption="満点" id="test_full_score"/>
        </x-bs.col3>
    </x-bs.row>
    </x-bs.card>

    <x-input.text caption="宿題達成度（%）" id="achievement"/>

    <x-input.textarea caption="達成・課題点" id="goodbad_point"/>

    <x-input.textarea caption="解決策" id="solution"/>

    <x-input.textarea caption="その他" id="others_comment"/>

    <x-bs.card>
        <x-bs.form-title>宿題</x-bs.form-title>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材１" id="homework_text1" :select2=true>
                    <option value="1">数学ドリル基本</option>
                    <option value="2">数学ドリル発展</option>
                    <option value="3">数学ドリル演習</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="homework_page1"/>
            </x-bs.col2>
        </x-bs.row>

        <x-input.select caption="単元１" id="homework_unit1" :select2=true>
            <option value="1">正負の数</option>
            <option value="2">比例</option>
            <option value="3">連立方程式</option>
        </x-input.select>

        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材２" id="homework_text2" :select2=true>
                    <option value="1">数学ドリル基本</option>
                    <option value="2">数学ドリル発展</option>
                    <option value="3">数学ドリル演習</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="homework_page2"/>
            </x-bs.col2>
        </x-bs.row>

        <x-input.select caption="単元２" id="homework_unit2" :select2=true>
            <option value="1">正負の数</option>
            <option value="2">比例</option>
            <option value="3">連立方程式</option>
        </x-input.select>
    </x-bs.card>

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
    <x-input.hidden id="report_id"/>

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