@extends('adminlte::page')

@section('title', '授業振替依頼')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>個別指導授業の振替依頼を行います。振替日は第３希望まで指定できます。</p>

    <x-input.select caption="授業日・時限" id="schedule_id" :select2=true onChange="selectChangeSchedule"
        :mastrData=$lesson_list :editData=$editData :select2Search=false :blank=true />

    <div v-cloak>
        <x-bs.table vShow="form.schedule_id" :hover=false :vHeader=true :smartPhone=false>
            <tr>
                <th>校舎</th>
                <td>@{{selectGetItem.campus_name}}</td>
            </tr>
            <tr>
                <th>コース</th>
                <td>@{{selectGetItem.course_name}}</td>
            </tr>
            <tr>
                <th>講師名</th>
                <td>@{{selectGetItem.tutor_name}}</td>
            </tr>
            <tr>
                <th>教科</th>
                <td>@{{selectGetItem.subject_name}}</td>
            </tr>
        </x-bs.table>
    </div>

    <div v-cloak v-show="form.schedule_id" class="callout callout-info mt-4 mb-4">
        <p>振替日は @{{selectGetItem.preferred_from}} ～ @{{selectGetItem.preferred_to}} の範囲で指定してください。</p>
    </div>

    <x-bs.form-title>振替希望日</x-bs.form-title>

    @for ($i = 1; $i <= 3; $i++) <x-bs.card>
        <p class="input-title">第{{$i}}希望日</p>

        {{-- 希望入力欄の選択 --}}
        <x-bs.form-group>
            <x-input.radio caption="担当講師の空きコマから選択" id="preferred{{$i}}_type-1" name="preferred{{$i}}_type" value="1"
                :checked=true :editData=$editData />
            <x-input.radio caption="フリー入力" id="preferred{{$i}}_type-2" name="preferred{{$i}}_type" value="2"
                :editData=$editData />
        </x-bs.form-group>
        {{-- 余白 --}}
        <div class="mb-3"></div>

        <x-input.select vShow="form.preferred{{$i}}_type == 1" id="preferred_date{{$i}}_select" :select2=true
            :select2Search=false :editData=$editData>
            {{-- vueで動的にプルダウンを作成 --}}
            <option v-for="item in selectGetItem.candidates" :value="item.id">
                @{{ item.value }}
            </option>
        </x-input.select>

        <x-input.date-picker vShow="form.preferred{{$i}}_type == 2" id="preferred_date{{$i}}_calender" />

        <x-input.select vShow="form.preferred{{$i}}_type == 2" id="preferred_date{{$i}}_period" caption="時限"
            :select2=true :editData=$editData :select2Search=false :blank=true>
            {{-- vueで動的にプルダウンを作成 --}}
            <option v-for="item in selectGetItemPeriod{{$i}}" :value="item.code">
                @{{ item.value }}
            </option>
        </x-input.select>

</x-bs.card>
@endfor

<x-input.textarea caption="振替理由／ご要望などはこちらへご記入ください" id="transfer_reason" :rules=$rules />

{{-- hidden --}}
<x-input.hidden id="campus_cd" :editData=$editData />
@for ($i = 1; $i <= 3; $i++)
<x-input.hidden id="preferred_date{{$i}}_period_bef" />
@endfor

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