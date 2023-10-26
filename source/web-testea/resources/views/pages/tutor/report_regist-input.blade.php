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
            <td>{{$regist_date->format('Y/m/d')}}</td>
        </tr>
        <tr>
            <th width="35%">授業日・時限</th>
            <td>{{$lesson_date->format('Y/m/d')}} {{$period_no}}限</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>{{$campus_name}}</td>
        </tr>
        <tr>
            <th>コース</th>
            <td>{{$course_name}}</td>
        </tr>
        {{-- 個別指導の場合 --}}
        <tr v-show="{{$course_kind}} == {{ App\Consts\AppConst::CODE_MASTER_42_1 }}">
            <th>生徒</th>
            <td>{{$student_name}}</td>
        </tr>
        {{-- 集団授業の場合 --}}
        <tr v-show="{{$course_kind}} == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}">
            <th>受講生徒名</th>
            <td>
                @foreach ($class_member_names as $class_member_name)
                    {{$class_member_name}}<br>
                @endforeach
            </td>
        </tr>
        <tr>
            <th>科目</th>
            <td>{{$subject_name}}</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>
    @else
    {{-- 登録時 --}}
    <x-bs.card>
        <x-input.select caption="授業日・時限" id="id" :select2=true onChange="selectChangeGet" 
            :rules=$rules :mastrData=$lesson_list :editData=$editData :select2Search=false :blank=true />
        {{-- 詳細を表示 --}}
        <x-bs.table vShow="form.id > 0" :hover=false :vHeader=true class="mb-4">
            <tr>
                <th>校舎</th>
                <td><span v-cloak>@{{selectGetItem.campus_name}}</span></td>
            </tr>
            <tr>
                <th>コース</th>
                <td><span v-cloak>@{{selectGetItem.course_name}}</span></td>
            </tr>
            {{-- 個別指導の場合 --}}
            <tr v-show="selectGetItem.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_1 }}">
                <th>生徒</th>
                <td><span v-cloak>@{{selectGetItem.student_name}}</span></td>
            </tr>
            {{-- 集団授業の場合 --}}
            <tr v-show="selectGetItem.course_kind == {{ App\Consts\AppConst::CODE_MASTER_42_2 }}">
                <th>受講生徒名</th>
                <td><span v-for="member in selectGetItem.class_member_name" v-cloak>@{{member}}<br></span></td>
            </tr>
            <tr>
                <th>科目</th>
                <td><span v-cloak>@{{selectGetItem.subject_name}}</span></td>
            </tr>
        </x-bs.table>
    </x-bs.card>
    @endif

    <x-input.text caption="今月の目標" id="monthly_goal" :rules=$rules :editData=$editData />

    <x-bs.form-title>授業内容</x-bs.form-title>
    @if (request()->routeIs('report_regist-edit'))
    {{-- 編集時 --}}
    @for ($i = 1; $i <= 2; $i++)
    <x-bs.card>
        {{-- <x-input.text id="id" onChange="selectChangeGet" :rules=$rules :editData=$editData/> --}}
        <x-bs.form-title>教材{{$i}}</x-bs.form-title>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材" id="lesson_text{{$i}}" :editData=$editData onChange="selectChangeGetCat"
                    :mastrData=$texts :rules=$rules :select2=true :select2Search=true :blank=true/>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="lesson_page{{$i}}" :rules=$rules :editData=$editData/>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他教材名（フリー入力）" id="lesson_text_name{{$i}}"
                    v-Show="form.lesson_text{{$i}}.endsWith('99')" :rules=$rules :editData=$editData/>
            </x-bs.col2>
        </x-bs.row>
        @for ($j = 1; $j <= 3; $j++) <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類{{$j}}" id="lesson_category{{$i}}_{{$j}}" :editData=$editData onChange="selectChangeGetUni"
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in $data['selectGetItemCatL' + {{$i}}].selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元{{$j}}" id="lesson_unit{{$i}}_{{$j}}" :editData=$editData
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in $data['selectGetItemUniL' + {{$i}} + '_' + {{$j}}].selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名{{$j}}（フリー入力）" id="lesson_category_name{{$i}}_{{$j}}"
                :rules=$rules v-Show="form.lesson_category{{$i}}_{{$j}}.endsWith('99')" :editData=$editData/>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="その他単元名{{$j}}（フリー入力）" id="lesson_unit_name{{$i}}_{{$j}}"
                :rules=$rules v-Show="form.lesson_unit{{$i}}_{{$j}}.endsWith('99')" :editData=$editData/>
            </x-bs.col2>
        </x-bs.row>
        @endfor
    </x-bs.card>
    @endfor
    @else
    {{-- 登録時 --}}
    @for ($i = 1; $i <= 2; $i++)
    <x-bs.card>
        <x-bs.form-title>教材{{$i}}</x-bs.form-title>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材" id="lesson_text{{$i}}" :editData=$editData onChange="selectChangeGetCat"
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in selectGetItem.selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="lesson_page{{$i}}" :rules=$rules/>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他教材名（フリー入力）" id="lesson_text_name{{$i}}"
                    v-Show="form.lesson_text{{$i}}.endsWith('99')" :rules=$rules/>
            </x-bs.col2>
        </x-bs.row>
        @for ($j = 1; $j <= 3; $j++) <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類{{$j}}" id="lesson_category{{$i}}_{{$j}}" :editData=$editData onChange="selectChangeGetUni"
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in $data['selectGetItemCatL' + {{$i}}].selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元{{$j}}" id="lesson_unit{{$i}}_{{$j}}" :editData=$editData
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in $data['selectGetItemUniL' + {{$i}} + '_' + {{$j}}].selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名{{$j}}（フリー入力）" id="lesson_category_name{{$i}}_{{$j}}"
                :rules=$rules v-Show="form.lesson_category{{$i}}_{{$j}}.endsWith('99')" />
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="その他単元名{{$j}}（フリー入力）" id="lesson_unit_name{{$i}}_{{$j}}"
                :rules=$rules v-Show="form.lesson_unit{{$i}}_{{$j}}.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        @endfor
    </x-bs.card>
    @endfor
    @endif

    <x-bs.card>
    <x-bs.form-title>確認テスト</x-bs.form-title>
    <x-input.text caption="内容" id="test_contents" :rules=$rules :editData=$editData/>
    <x-bs.row>
        <x-bs.col3>
            <x-input.text caption="得点" id="test_score" :rules=$rules :editData=$editData/>
        </x-bs.col3>
            <x-bs.form-title></x-bs.form-title>
            <p class="edit-disp-indent">／　</p>
        <x-bs.col3>
            <x-input.text caption="満点" id="test_full_score" :rules=$rules :editData=$editData/>
        </x-bs.col3>
    </x-bs.row>
    </x-bs.card>

    <x-input.text caption="宿題達成度（%）" id="achievement" :rules=$rules :editData=$editData/>

    <x-input.textarea caption="達成・課題点" id="goodbad_point" :rules=$rules :editData=$editData/>

    <x-input.textarea caption="解決策" id="solution" :rules=$rules :editData=$editData/>

    <x-input.textarea caption="その他" id="others_comment" :rules=$rules :editData=$editData/>

    <x-bs.form-title>宿題</x-bs.form-title>
    @for ($i = 1; $i <= 2; $i++)
    <x-bs.card>
        <x-bs.form-title>教材{{$i}}</x-bs.form-title>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材" id="homework_text{{$i}}" :editData=$editData onChange="selectChangeGetCatHome"
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in selectGetItem.selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="homework_page{{$i}}" :rules=$rules/>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他教材名（フリー入力）" id="homework_text_name{{$i}}"
                    :rules=$rules v-Show="form.homework_text{{$i}}.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        @for ($j = 1; $j <= 3; $j++) <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類{{$j}}" id="homework_category{{$i}}_{{$j}}" :editData=$editData onChange="selectChangeGetUniHome"
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in $data['selectGetItemCatHomeL' + {{$i}}].selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元{{$j}}" id="homework_unit{{$i}}_{{$j}}" :editData=$editData
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in $data['selectGetItemUniHomeL' + {{$i}} + '_' + {{$j}}].selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名{{$j}}（フリー入力）" id="homework_category_name{{$i}}_{{$j}}"
                    :rules=$rules v-Show="form.homework_category{{$i}}_{{$j}}.endsWith('99')" />
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="その他単元名{{$j}}（フリー入力）" id="homework_unit_name{{$i}}_{{$j}}"
                    :rules=$rules v-Show="form.homework_unit{{$i}}_{{$j}}.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        @endfor
    </x-bs.card>
    @endfor

    @if (request()->routeIs('report_regist-edit'))
    {{-- 編集時 承認ステータス・管理者コメント--}}
    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th>承認ステータス</th>
            <td>{{$status}}</td>
        </tr>
        <tr>
            <th>管理者コメント</th>
            {{-- nl2br: 改行 --}}
            <td class="nl2br">{{$admin_comment}}</td>
        </tr>
    </x-bs.table>
    {{-- hidden --}}
    <x-input.hidden id="report_id" :editData=$editData/>
    @endif

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