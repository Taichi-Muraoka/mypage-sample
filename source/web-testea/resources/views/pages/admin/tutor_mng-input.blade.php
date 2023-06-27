@extends('adminlte::page')

@section('title', (request()->routeIs('tutor_mng-edit')) ? '講師編集' : '講師登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('tutor_mng-edit'))
    {{-- 編集時 --}}
    <p>以下の講師について、編集を行います。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="15%">講師ID</th>
            <td>101</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>講師の基本情報を登録します。</p>
    @endif

    {{-- 共通項目 --}}
    <x-input.text caption="講師名" id="name" :rules=$rules />
    <x-input.text caption="電話番号" id="tel" :rules=$rules />
    <x-input.text caption="メールアドレス" id="email" :rules=$rules />
    <x-input.text caption="授業時給（個別）" id="hourly_wage_p" :rules=$rules :editData=$editData/>
    <x-input.text caption="授業時給（集団）" id="hourly_wage_g" :rules=$rules :editData=$editData/>
    <x-input.date-picker caption="勤務開始日" id="enter_date" />

    <x-bs.card>
        <x-bs.form-title>科目選択（小）</x-bs.form-title>

        <x-bs.form-group name="subject_groups_p">
                {{-- 教科チェックボックス --}}
                @for ($i = 0; $i < count($subjectGroup); $i++)
                <x-input.checkbox :caption="$subjectGroup[$i]"
                        :id="'subject_group_p' . $subjectGroup[$i]"
                        name="subject_groups_p" :value="$subjectGroup[$i]" />
                @endfor
        </x-bs.form-group>
    </x-bs.card>
    <x-bs.card>
        <x-bs.form-title>科目選択（中）</x-bs.form-title>

        <x-bs.form-group name="subject_groups_j">
                {{-- 教科チェックボックス --}}
                @for ($i = 0; $i < count($subjectGroup); $i++)
                <x-input.checkbox :caption="$subjectGroup[$i]"
                        :id="'subject_group_j' . $subjectGroup[$i]"
                        name="subject_groups_j" :value="$subjectGroup[$i]" />
                @endfor
        </x-bs.form-group>
    </x-bs.card>
    <x-bs.card>
        <x-bs.form-title>科目選択（高）</x-bs.form-title>

        <x-bs.form-group name="subject_groups_h">
                {{-- 教科チェックボックス --}}
                @for ($i = 0; $i < count($subjectGroup); $i++)
                <x-input.checkbox :caption="$subjectGroup[$i]"
                        :id="'subject_group_h' . $subjectGroup[$i]"
                        name="subject_groups_h" :value="$subjectGroup[$i]" />
                @endfor
        </x-bs.form-group>
    </x-bs.card>

    @if (request()->routeIs('tutor_mng-edit'))
    {{-- 編集時 講師ステータス--}}
    <x-input.select id="tutor_status" caption="講師ステータス" :select2=false >
        <option value="1">在籍</option>
        <option value="2">退職処理中</option>
        <option value="3">退職</option>
    </x-input.select>
    @endif

    {{-- hidden --}}
    <x-input.hidden id="tutor_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            @if (request()->routeIs('tutor_mng-edit'))
            {{-- 編集時 --}}
            <x-button.back url="{{route('tutor_mng-detail', 1)}}"/>
            <div class="d-flex justify-content-end">
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.back />
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>

</x-bs.card>

@stop