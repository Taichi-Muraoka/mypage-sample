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
            <th>登録日</th>
            <td>2023/05/15</td>
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
            <th>コース</th>
            <td>個別指導</td>
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
        <x-input.select caption="授業日・時限" id="id" :select2=true  :editData=$editData>
            <option value="1">2023/04/24 5限</option>
            <option value="2">2023/04/17 5限</option>
            <option value="3">2023/04/10 5限</option>
            <option value="4">2023/04/03 5限</option>
        </x-input.select>

        {{-- 詳細を表示 --}}
        <x-bs.table vShow="form.id > 0" :hover=false :vHeader=true class="mb-4">
            <tr>
                <th>校舎</th>
                <td><span v-cloak>久我山</span></td>
            </tr>
            <tr>
                <th>コース</th>
                <td><span v-cloak>個別指導コース</span></td>
            </tr>
            <tr>
                <th>生徒</th>
                <td><span v-cloak>CWテスト生徒１</span></td>
            </tr>
            <tr>
                <th>科目</th>
                <td><span v-cloak>数学</span></td>
            </tr>
        </x-bs.table>
    </x-bs.card>
    @endif

    <x-input.text caption="今月の目標" id="monthly_goal"/>

    <x-bs.form-title>授業内容</x-bs.form-title>
    <x-bs.card>
        <x-bs.form-title>教材１</x-bs.form-title>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材" id="lesson_text1" :select2=true>
                    <option value="07102001">中１数学ドリル基本</option>
                    <option value="07102002">中１数学ドリル演習</option>
                    <option value="07102099">中１数学その他</option>
                    <option value="08102001">中２数学ドリル基本</option>
                    <option value="08102002">中２数学ドリル演習</option>
                    <option value="08102099">中２数学その他</option>
                    <option value="09102001">中３数学ドリル基本</option>
                    <option value="09102002">中３数学ドリル演習</option>
                    <option value="09102003">中３数学受験対策</option>
                    <option value="09102099">中３数学その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="lesson_page1"/>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他教材名（フリー入力）" id="lesson_text_name1"
                    v-Show="form.lesson_text1.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類１" id="lesson_category1_1" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元１" id="lesson_unit1_1" :select2=true>
                    <option value="01">符号のついた数</option>
                    <option value="02">数の大小</option>
                    <option value="03">加法と減法</option>
                    <option value="03">乗法と除法</option>
                    <option value="04">四則の混じった計算</option>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名１（フリー入力）" id="lesson_category_name1_1"
                    v-Show="form.lesson_category1_1.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名１（フリー入力）" id="lesson_unit_name1_1"
                        v-Show="form.lesson_unit1_1.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類２" id="lesson_category1_2" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元２" id="lesson_unit1_2" :select2=true>
                    <option value="01">文字の使用</option>
                    <option value="02">文字を使った式の表し方</option>
                    <option value="03">代入と式の値</option>
                    <option value="04">１次式の計算</option>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名２（フリー入力）" id="lesson_category_name1_2"
                    v-Show="form.lesson_category1_2.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名２（フリー入力）" id="lesson_unit_name1_2"
                        v-Show="form.lesson_unit1_2.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類３" id="lesson_category1_3" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元３" id="lesson_unit1_3" :select2=true>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名３（フリー入力）" id="lesson_category_name1_3"
                    v-Show="form.lesson_category1_3.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名３（フリー入力）" id="lesson_unit_name1_3"
                        v-Show="form.lesson_unit1_3.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
    </x-bs.card>

    <x-bs.card>
        <x-bs.form-title>教材２</x-bs.form-title>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材" id="lesson_text2" :select2=true>
                    <option value="07102001">中１数学ドリル基本</option>
                    <option value="07102002">中１数学ドリル演習</option>
                    <option value="07102099">中１数学その他</option>
                    <option value="08102001">中２数学ドリル基本</option>
                    <option value="08102002">中２数学ドリル演習</option>
                    <option value="08102099">中２数学その他</option>
                    <option value="09102001">中３数学ドリル基本</option>
                    <option value="09102002">中３数学ドリル演習</option>
                    <option value="09102003">中３数学受験対策</option>
                    <option value="09102099">中３数学その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="lesson_page2"/>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他教材名（フリー入力）" id="lesson_text_name2"
                    v-Show="form.lesson_text1.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類１" id="lesson_category2_1" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元１" id="lesson_unit2_1" :select2=true>
                    <option value="01">符号のついた数</option>
                    <option value="02">数の大小</option>
                    <option value="03">加法と減法</option>
                    <option value="03">乗法と除法</option>
                    <option value="04">四則の混じった計算</option>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名１（フリー入力）" id="lesson_category_name2_1"
                    v-Show="form.lesson_category2_1.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名１（フリー入力）" id="lesson_unit_name2_1"
                        v-Show="form.lesson_unit2_1.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類２" id="lesson_category2_2" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元２" id="lesson_unit2_2" :select2=true>
                    <option value="01">文字の使用</option>
                    <option value="02">文字を使った式の表し方</option>
                    <option value="03">代入と式の値</option>
                    <option value="04">１次式の計算</option>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名２（フリー入力）" id="lesson_category_name2_2"
                    v-Show="form.lesson_category2_2.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名２（フリー入力）" id="lesson_unit_name2_2"
                        v-Show="form.lesson_unit2_2.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類３" id="lesson_category2_3" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元３" id="lesson_unit2_3" :select2=true>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名３（フリー入力）" id="lesson_category_name2_3"
                    v-Show="form.lesson_category2_3.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名３（フリー入力）" id="lesson_unit_name2_3"
                        v-Show="form.lesson_unit2_3.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
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

    <x-bs.form-title>宿題</x-bs.form-title>
    <x-bs.card>
        <x-bs.form-title>教材１</x-bs.form-title>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材" id="homework_text1" :select2=true>
                    <option value="07102001">中１数学ドリル基本</option>
                    <option value="07102002">中１数学ドリル演習</option>
                    <option value="07102099">中１数学その他</option>
                    <option value="08102001">中２数学ドリル基本</option>
                    <option value="08102002">中２数学ドリル演習</option>
                    <option value="08102099">中２数学その他</option>
                    <option value="09102001">中３数学ドリル基本</option>
                    <option value="09102002">中３数学ドリル演習</option>
                    <option value="09102003">中３数学受験対策</option>
                    <option value="09102099">中３数学その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="homework_page1"/>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他教材名（フリー入力）" id="homework_text_name1"
                    v-Show="form.homework_text1.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類１" id="homework_category1_1" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元１" id="homework_unit1_1" :select2=true>
                    <option value="01">符号のついた数</option>
                    <option value="02">数の大小</option>
                    <option value="03">加法と減法</option>
                    <option value="03">乗法と除法</option>
                    <option value="04">四則の混じった計算</option>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名１（フリー入力）" id="homework_category_name1_1"
                    v-Show="form.homework_category1_1.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名１（フリー入力）" id="homework_unit_name1_1"
                        v-Show="form.homework_unit1_1.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類２" id="homework_category1_2" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元２" id="homework_unit1_2" :select2=true>
                    <option value="01">文字の使用</option>
                    <option value="02">文字を使った式の表し方</option>
                    <option value="03">代入と式の値</option>
                    <option value="04">１次式の計算</option>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名２（フリー入力）" id="homework_category_name1_2"
                    v-Show="form.homework_category1_2.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名２（フリー入力）" id="homework_unit_name1_2"
                        v-Show="form.homework_unit1_2.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類３" id="homework_category1_3" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元３" id="homework_unit1_3" :select2=true>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名３（フリー入力）" id="homework_category_name1_3"
                    v-Show="form.homework_category1_3.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名３（フリー入力）" id="homework_unit_name1_3"
                        v-Show="form.homework_unit1_3.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
    </x-bs.card>

    <x-bs.card>
        <x-bs.form-title>教材２</x-bs.form-title>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材" id="homework_text2" :select2=true>
                    <option value="07102001">中１数学ドリル基本</option>
                    <option value="07102002">中１数学ドリル演習</option>
                    <option value="07102099">中１数学その他</option>
                    <option value="08102001">中２数学ドリル基本</option>
                    <option value="08102002">中２数学ドリル演習</option>
                    <option value="08102099">中２数学その他</option>
                    <option value="09102001">中３数学ドリル基本</option>
                    <option value="09102002">中３数学ドリル演習</option>
                    <option value="09102003">中３数学受験対策</option>
                    <option value="09102099">中３数学その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="homework_page2"/>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他教材名（フリー入力）" id="homework_text_name2"
                    v-Show="form.homework_text1.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類１" id="homework_category2_1" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元１" id="homework_unit2_1" :select2=true>
                    <option value="01">符号のついた数</option>
                    <option value="02">数の大小</option>
                    <option value="03">加法と減法</option>
                    <option value="03">乗法と除法</option>
                    <option value="04">四則の混じった計算</option>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名１（フリー入力）" id="homework_category_name2_1"
                    v-Show="form.homework_category2_1.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名１（フリー入力）" id="homework_unit_name2_1"
                        v-Show="form.homework_unit2_1.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類２" id="homework_category2_2" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元２" id="homework_unit2_2" :select2=true>
                    <option value="01">文字の使用</option>
                    <option value="02">文字を使った式の表し方</option>
                    <option value="03">代入と式の値</option>
                    <option value="04">１次式の計算</option>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名２（フリー入力）" id="homework_category_name2_2"
                    v-Show="form.homework_category2_2.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名２（フリー入力）" id="homework_unit_name2_2"
                        v-Show="form.homework_unit2_2.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類３" id="homework_category2_3" :select2=true>
                    <option value="0710201">正負の数</option>
                    <option value="0710202">文字と式</option>
                    <option value="0710203">方程式</option>
                    <option value="0710204">比例と反比例</option>
                    <option value="0710205">平面図形</option>
                    <option value="0710206">空間図形</option>
                    <option value="0710207">データの分析と活用</option>
                    <option value="0710299">その他</option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元３" id="homework_unit2_3" :select2=true>
                    <option value="99">その他</option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名３（フリー入力）" id="homework_category_name2_3"
                    v-Show="form.homework_category2_3.endsWith('99')" />
        </x-bs.col2>
        <x-bs.col2>
                    <x-input.text caption="その他単元名３（フリー入力）" id="homework_unit_name2_3"
                        v-Show="form.homework_unit2_3.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
    </x-bs.card>

    @if (request()->routeIs('report_regist-edit'))
    {{-- 編集時 承認ステータス・管理者コメント--}}
    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th>承認ステータス</th>
            <td></td>
        </tr>
        <tr>
            <th>管理者コメント</th>
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