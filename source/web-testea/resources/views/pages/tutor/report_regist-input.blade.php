@extends('adminlte::page')
@inject('formatter','App\Libs\CommonDateFormat')

@section('title', (request()->routeIs('report_regist-edit')) ? '授業報告書編集' : '授業報告書登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業報告書の{{(request()->routeIs('report_regist-edit')) ? '変更' : '登録'}}を行います。</p>

    @if (request()->routeIs('report_regist-edit'))
    {{-- 編集時 --}}
    {{-- hidden 退避用--}}
    <x-input.hidden id="id" :editData=$editData />
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th>登録日</th>
            <td>{{$regist_date->format('Y/m/d')}}</td>
        </tr>
        <tr>
            <th width="35%">授業日・時限</th>
            <td>{{$formatter::formatYmdDay($lesson_date)}} {{$period_no}}限</td>
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
    @for ($i = 1; $i <= 2; $i++)
    <x-bs.card>
        <x-bs.form-title>教材{{$i}}</x-bs.form-title>
        <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="教材" id="text_cd_L{{$i}}" :editData=$editData onChange="selectChangeGetCat"
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in selectGetItem.selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
                {{-- hidden 退避用--}}
                <x-input.hidden id="bef_text_cd_L{{$i}}" :editData=$editData />
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="text_page_L{{$i}}" :rules=$rules :editData=$editData/>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他教材名（フリー入力）" id="text_name_L{{$i}}"
                    v-Show="form.text_cd_L{{$i}}.endsWith('99')" :rules=$rules :editData=$editData/>
            </x-bs.col2>
        </x-bs.row>
        @for ($j = 1; $j <= 3; $j++) <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類{{$j}}" id="unit_category_cd{{$j}}_L{{$i}}" :editData=$editData onChange="selectChangeGetUni"
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in $data['selectGetItemCatL' + {{$i}}].selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
                {{-- hidden 退避用--}}
                <x-input.hidden id="bef_unit_category_cd{{$j}}_L{{$i}}" :editData=$editData />
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元{{$j}}" id="unit_cd{{$j}}_L{{$i}}" :editData=$editData
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in $data['selectGetItemUni' + {{$j}} + '_L' + {{$i}}].selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
                {{-- hidden 退避用--}}
                <x-input.hidden id="bef_unit_cd{{$j}}_L{{$i}}" :editData=$editData />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名{{$j}}（フリー入力）" id="category_name{{$j}}_L{{$i}}"
                :rules=$rules v-Show="form.unit_category_cd{{$j}}_L{{$i}}.endsWith('99')" :editData=$editData/>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="その他単元名{{$j}}（フリー入力）" id="unit_name{{$j}}_L{{$i}}"
                :rules=$rules v-Show="form.unit_cd{{$j}}_L{{$i}}.endsWith('99')" :editData=$editData/>
            </x-bs.col2>
        </x-bs.row>
        @endfor
    </x-bs.card>
    @endfor

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
                <x-input.select caption="教材" id="text_cd_H{{$i}}" :editData=$editData onChange="selectChangeGetCat"
                    :rules=$rules :select2=true :select2Search=true :blank=true :editData=$editData>
                    <option v-for="item in selectGetItem.selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
                {{-- hidden 退避用--}}
                <x-input.hidden id="bef_text_cd_H{{$i}}" :editData=$editData />
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="ページ" id="text_page_H{{$i}}" :rules=$rules :editData=$editData/>
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他教材名（フリー入力）" id="text_name_H{{$i}}"
                    :rules=$rules :editData=$editData v-Show="form.text_cd_H{{$i}}.endsWith('99')" />
            </x-bs.col2>
        </x-bs.row>
        @for ($j = 1; $j <= 3; $j++) <x-bs.row>
            <x-bs.col2>
                <x-input.select caption="単元分類{{$j}}" id="unit_category_cd{{$j}}_H{{$i}}" onChange="selectChangeGetUni"
                    :rules=$rules :select2=true :select2Search=true :blank=true :editData=$editData>
                    <option v-for="item in $data['selectGetItemCatH' + {{$i}}].selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
                {{-- hidden 退避用--}}
                <x-input.hidden id="bef_unit_category_cd{{$j}}_H{{$i}}" :editData=$editData />
            </x-bs.col2>
            <x-bs.col2>
                <x-input.select caption="単元{{$j}}" id="unit_cd{{$j}}_H{{$i}}" :editData=$editData
                    :rules=$rules :select2=true :select2Search=true :blank=true>
                    <option v-for="item in $data['selectGetItemUni' + {{$j}} + '_H' + {{$i}}].selectItems" :value="item.code">
                        @{{ item.value }}
                    </option>
                </x-input.select>
                {{-- hidden 退避用--}}
                <x-input.hidden id="bef_unit_cd{{$j}}_H{{$i}}" :editData=$editData />
            </x-bs.col2>
        </x-bs.row>
        <x-bs.row>
            <x-bs.col2>
                <x-input.text caption="その他単元分類名{{$j}}（フリー入力）" id="category_name{{$j}}_H{{$i}}"
                    :rules=$rules v-Show="form.unit_category_cd{{$j}}_H{{$i}}.endsWith('99')" :editData=$editData/>
            </x-bs.col2>
            <x-bs.col2>
                <x-input.text caption="その他単元名{{$j}}（フリー入力）" id="unit_name{{$j}}_H{{$i}}"
                    :rules=$rules v-Show="form.unit_cd{{$j}}_H{{$i}}.endsWith('99')" :editData=$editData/>
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