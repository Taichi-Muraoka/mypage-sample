@extends('adminlte::page')

@section('title', '授業振替依頼')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>個別指導授業の振替依頼を行います。振替日は第３希望まで指定できます。</p>

    <x-input.select caption="授業日・時限" id="id" :select2=true :editData="$editData">
        <option value="1">2023/01/30 3限</option>
        <option value="2">2023/01/30 4限</option>
        <option value="3">2023/01/31 2限</option>
    </x-input.select>

    <div v-cloak>
        <x-bs.table vShow="form.id" :hover=false :vHeader=true>
            <tr>
                <th>校舎</th>
                <td>久我山</td>
            </tr>
            <tr>
                <th>コース</th>
                <td>個別指導コース</td>
            </tr>
            <tr>
                <th>講師名</th>
                <td>CWテスト教師１０１</td>
            </tr>
        </x-bs.table>
    </div>

    <x-bs.form-title>振替希望日</x-bs.form-title>
    {{-- 第１希望日 --}}
    {{-- id="preferred_date1" --}}
    <x-bs.card>
        <p class="input-title">第１希望日</p>
        
        {{-- 希望入力欄の選択 --}}
        <x-bs.form-group>
            <x-input.radio caption="担当講師の空きコマから選択" id="preferred1_type-1" name="preferred1_type" value="1" :checked=true :editData=$editData />
            <x-input.radio caption="フリー入力" id="preferred1_type-2" name="preferred1_type" value="2" :editData=$editData />
        </x-bs.form-group>
        {{-- 余白 --}}
        <div class="mb-3"></div>

        <x-input.select id="preferred_date1_select" :select2=true :select2Search=false :editData=$editData
        vShow="form.preferred1_type == 1" >
            <option value="1">2023/03/20 4限</option>
            <option value="2">2023/03/20 5限</option>
            <option value="3">2023/03/27 4限</option>
            <option value="4">2023/03/27 5限</option>
            <option value="5">2023/04/03 4限</option>
            <option value="6">2023/04/03 5限</option>
            <option value="7">2023/04/10 4限</option>
            <option value="8">2023/04/10 5限</option>
        </x-input.select>

        <x-input.date-picker id="preferred_date1_calender" vShow="form.preferred1_type == 2" />

        <x-input.select caption="時限" id="preferred_date1_period" :select2=true :select2Search=false :editData=$editData
        vShow="form.preferred1_type == 2">
            <option value="1">1限</option>
            <option value="2">2限</option>
            <option value="3">3限</option>
            <option value="4">4限</option>
            <option value="5">5限</option>
            <option value="6">6限</option>
            <option value="7">7限</option>
            <option value="8">8限</option>
        </x-input.select>
    </x-bs.card>

    {{-- 第２希望日 --}}
    {{-- id="preferred_date2" --}}
    <x-bs.card>
        <p class="input-title">第２希望日</p>
        {{-- 希望入力欄の選択 --}}
        <x-bs.form-group>
            <x-input.radio caption="担当講師の空きコマから選択" id="preferred2_type-1" name="preferred2_type" value="1" :checked=true :editData=$editData />
            <x-input.radio caption="フリー入力" id="preferred2_type-2" name="preferred2_type" value="2" :editData=$editData />
        </x-bs.form-group>
        {{-- 余白 --}}
        <div class="mb-3"></div>

        <x-input.select id="preferred_date2_select" :select2=true :select2Search=false :editData=$editData
        vShow="form.preferred2_type == 1" >
            <option value="1">2023/03/20 4限</option>
            <option value="2">2023/03/20 5限</option>
            <option value="3">2023/03/27 4限</option>
            <option value="4">2023/03/27 5限</option>
            <option value="5">2023/04/03 4限</option>
            <option value="6">2023/04/03 5限</option>
            <option value="7">2023/04/10 4限</option>
            <option value="8">2023/04/10 5限</option>
        </x-input.select>

        <x-input.date-picker id="preferred_date2_calender" vShow="form.preferred2_type == 2" />

        <x-input.select caption="時限" id="preferred_date2_period" :select2=true :select2Search=false :editData=$editData
        vShow="form.preferred2_type == 2">
            <option value="1">1限</option>
            <option value="2">2限</option>
            <option value="3">3限</option>
            <option value="4">4限</option>
            <option value="5">5限</option>
            <option value="6">6限</option>
            <option value="7">7限</option>
            <option value="8">8限</option>
        </x-input.select>
    </x-bs.card>

    {{-- 第３希望日 --}}
    {{-- id="preferred_date3" --}}
    <x-bs.card>
        <p class="input-title">第３希望日</p>
        {{-- 希望入力欄の選択 --}}
        <x-bs.form-group>
            <x-input.radio caption="担当講師の空きコマから選択" id="preferred3_type-1" name="preferred3_type" value="1" :checked=true :editData=$editData />
            <x-input.radio caption="フリー入力" id="preferred3_type-2" name="preferred3_type" value="2" :editData=$editData />
        </x-bs.form-group>
        {{-- 余白 --}}
        <div class="mb-3"></div>

        <x-input.select id="preferred_date3_select" :select2=true :select2Search=false :editData=$editData
        vShow="form.preferred3_type == 1" >
            <option value="1">2023/03/20 4限</option>
            <option value="2">2023/03/20 5限</option>
            <option value="3">2023/03/27 4限</option>
            <option value="4">2023/03/27 5限</option>
            <option value="5">2023/04/03 4限</option>
            <option value="6">2023/04/03 5限</option>
            <option value="7">2023/04/10 4限</option>
            <option value="8">2023/04/10 5限</option>
        </x-input.select>

        <x-input.date-picker id="preferred_date3_calender" vShow="form.preferred3_type == 2" />

        <x-input.select caption="時限" id="preferred_date3_period" :select2=true :select2Search=false :editData=$editData
        vShow="form.preferred3_type == 2" >
            <option value="1">1限</option>
            <option value="2">2限</option>
            <option value="3">3限</option>
            <option value="4">4限</option>
            <option value="5">5限</option>
            <option value="6">6限</option>
            <option value="7">7限</option>
            <option value="8">8限</option>
        </x-input.select>

    </x-bs.card>

    <x-input.textarea caption="振替理由／ご要望などはこちらへご記入ください" id="transfer_reason" :rules=$rules />

    {{-- hidden --}}
    <x-input.hidden id="transfer_student_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 登録時 --}}
            <x-button.submit-new />

        </div>
    </x-slot>

</x-bs.card>

@stop