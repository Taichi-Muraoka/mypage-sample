@extends('adminlte::page')

@section('title', '授業振替依頼')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>個別指導授業の振替依頼を行います。振替日は第３希望まで指定できます。</p>

    {{-- hidden --}}
    <x-input.hidden id="tutor_id" :editData=$editData />
    <x-input.hidden id="monthly_count" :editData=$editData />
    <x-input.hidden id="skip_count" :editData=$editData />

    <x-input.select caption="生徒名" id="student_id" :select2=true :editData=$editData :mastrData=$students
        :editData=$editData :select2Search=true onChange="selectChangeStudent" />

    <div v-show="form.monthly_count >= form.skip_count">
        <p class="alert-msg" id="monthly_message"></p>
    </div>

    <x-input.select caption="授業日・時限" id="schedule_id" :select2=true onChange="selectChangeSchedule" :editData=$editData
        :select2Search=false :blank=true>
        {{-- vueで動的にプルダウンを作成 --}}
        <option v-for="item in selectGetItemSchedule" :value="item.id">
            @{{ item.value }}
        </option>
    </x-input.select>

    <div v-cloak>
        <x-bs.table vShow="form.schedule_id" :hover=false :vHeader=true :smartPhone=true>
            <tr>
                <th>校舎</th>
                <td>
                    <div id="campus_name"></div>
                </td>
            </tr>
            <tr>
                <th>コース</th>
                <td>
                    <div id="course_name"></div>
                </td>
            </tr>
            <tr>
                <th>教科</th>
                <td>
                    <div id="subject_name"></div>
                </td>
        </x-bs.table>
    </div>

    <div v-show="form.schedule_id" class="callout callout-info mt-4 mb-4">
        <p id="preferred_range"></p>
    </div>

    <x-bs.form-title>振替希望日</x-bs.form-title>
    @for ($i = 1; $i <= 3; $i++)
    <x-bs.card>
        <x-input.date-picker caption="第{{$i}}希望日" id="preferred_date{{$i}}_calender" />

        <x-input.select caption="時限" id="preferred_date{{$i}}_period" :select2=true :select2Search=false :blank=true
            :editData=$editData>
            {{-- vueで動的にプルダウンを作成 --}}
            <option v-for="item in selectGetItemPeriod{{$i}}" :value="item.code">
                @{{ item.value }}
            </option>
        </x-input.select>
    </x-bs.card>
    @endfor

    <x-input.textarea caption="振替理由／連絡事項など" id="transfer_reason" :rules=$rules />

    <x-bs.callout title="振替調整の注意事項" type="warning">
        同一生徒への振替希望については、月{{($editData['skip_count'] - 1)}}回まで管理者の承認なしで調整可能です。<br>
        {{($editData['skip_count'])}}回目からは管理者に依頼が送られ、管理者のチェック・承認が必要となります。<br>
    </x-bs.callout>

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