@extends('adminlte::page')

@section('title', '授業報告書編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業報告書の変更を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th>登録日</th>
            <td>2023/05/15</td>
        </tr>
        <tr>
            <th>講師名</th>
            <td>CWテスト教師１０１</td>
        </tr>
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

    <x-input.text caption="今月の目標" id="monthly_goal" :rules=$rules :editData=$editData />

    <x-bs.card>
        <x-bs.form-title>授業内容</x-bs.form-title>
        <x-bs.row>
            <x-bs.col3>
                <x-input.select caption="教材１" id="lesson_text1" :select2=true  :editData=$editData>
                    <option value="1">数学ドリル基本</option>
                    <option value="2">数学ドリル発展</option>
                    <option value="3">数学ドリル演習</option>
                </x-input.select>
            </x-bs.col3>

                <x-bs.form-title></x-bs.form-title>
                <p class="edit-disp-indent">／　</p>

                <x-input.text caption="ページ" id="lesson_page1" :rules=$rules :editData=$editData />

                <x-bs.form-title></x-bs.form-title>
                <p class="edit-disp-indent">／　</p>

            <x-bs.col3>
                <x-input.select caption="単元１" id="lesson_unit1" :select2=true  :editData=$editData>
                    <option value="1">正負の数</option>
                    <option value="2">比例</option>
                    <option value="3">連立方程式</option>
                </x-input.select>
            </x-bs.col3>
        </x-bs.row>

        <x-bs.row>
            <x-bs.col3>
                <x-input.select caption="教材２" id="lesson_text2" :select2=true  :editData=$editData>
                    <option value="1">数学ドリル基本</option>
                    <option value="2">数学ドリル発展</option>
                    <option value="3">数学ドリル演習</option>
                </x-input.select>
            </x-bs.col3>

                <x-bs.form-title></x-bs.form-title>
                <p class="edit-disp-indent">／　</p>

                <x-input.text caption="ページ" id="lesson_page2" :rules=$rules :editData=$editData />

                <x-bs.form-title></x-bs.form-title>
                <p class="edit-disp-indent">／　</p>

            <x-bs.col3>
                <x-input.select caption="単元２" id="lesson_unit2" :select2=true  :editData=$editData>
                    <option value="1">正負の数</option>
                    <option value="2">比例</option>
                    <option value="3">連立方程式</option>
                </x-input.select>
            </x-bs.col3>
        </x-bs.row>
    </x-bs.card>

    <x-bs.card>
    <x-bs.form-title>確認テスト</x-bs.form-title>
    <x-input.text caption="内容" id="test_contents" :rules=$rules :editData=$editData />
    <x-bs.row>
        <x-bs.col3>
            <x-input.text caption="得点" id="test_score" :rules=$rules :editData=$editData />
        </x-bs.col3>
            <x-bs.form-title></x-bs.form-title>
            <p class="edit-disp-indent">／　</p>
        <x-bs.col3>
            <x-input.text caption="満点" id="test_full_score" :rules=$rules :editData=$editData />
        </x-bs.col3>
    </x-bs.row>
    </x-bs.card>

    <x-input.text caption="宿題達成度（%）" id="achievement" :rules=$rules :editData=$editData />

    <x-input.textarea caption="達成・課題点" id="goodbad_point" :rules=$rules :editData=$editData />

    <x-input.textarea caption="解決策" id="solution" :rules=$rules :editData=$editData />

    <x-input.textarea caption="その他" id="others_comment" :rules=$rules :editData=$editData />

    <x-bs.card>
        <x-bs.form-title>宿題</x-bs.form-title>
        <x-bs.row>
            <x-bs.col3>
                <x-input.select caption="教材１" id="homework_text1" :select2=true  :editData=$editData>
                    <option value="1">数学ドリル基本</option>
                    <option value="2">数学ドリル発展</option>
                    <option value="3">数学ドリル演習</option>
                </x-input.select>
            </x-bs.col3>

                <x-bs.form-title></x-bs.form-title>
                <p class="edit-disp-indent">／　</p>

                <x-input.text caption="ページ" id="homework_page1" :rules=$rules :editData=$editData />

                <x-bs.form-title></x-bs.form-title>
                <p class="edit-disp-indent">／　</p>

            <x-bs.col3>
                <x-input.select caption="単元１" id="homework_unit1" :select2=true  :editData=$editData>
                    <option value="1">正負の数</option>
                    <option value="2">比例</option>
                    <option value="3">連立方程式</option>
                </x-input.select>
            </x-bs.col3>
        </x-bs.row>

        <x-bs.row>
            <x-bs.col3>
                <x-input.select caption="教材２" id="homework_text2" :select2=true  :editData=$editData>
                    <option value="1">数学ドリル基本</option>
                    <option value="2">数学ドリル発展</option>
                    <option value="3">数学ドリル演習</option>
                </x-input.select>
            </x-bs.col3>

                <x-bs.form-title></x-bs.form-title>
                <p class="edit-disp-indent">／　</p>

                <x-input.text caption="ページ" id="homework_page2" :rules=$rules :editData=$editData />

                <x-bs.form-title></x-bs.form-title>
                <p class="edit-disp-indent">／　</p>

            <x-bs.col3>
                <x-input.select caption="単元２" id="homework_unit2" :select2=true  :editData=$editData>
                    <option value="1">正負の数</option>
                    <option value="2">比例</option>
                    <option value="3">連立方程式</option>
                </x-input.select>
            </x-bs.col3>
        </x-bs.row>
    </x-bs.card>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select caption="承認ステータス" id="status" :select2=true :editData=$editData>
        <option value="1">承認待ち</option>
        <option value="2">承認</option>
        <option value="3">差戻し</option>
    </x-input.select>

    <x-input.textarea caption="管理者コメント" id="admin_comment" :rules=$rules :editData=$editData />

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