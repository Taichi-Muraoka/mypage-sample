@extends('adminlte::page')

@section('title', '振替情報登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>個別指導授業の振替スケジュール登録を行います。</p>

    {{-- hidden --}}
    <x-input.hidden id="campus_cd" />

    <x-input.select caption="生徒名" id="student_id" :select2=true :mastrData=$students :editData=$editData
        :select2Search=true onChange="selectChangeStudent" />

    <x-input.select caption="授業日・時限" id="schedule_id" :select2=true onChange="selectChangeSchedule"
        :select2Search=false :blank=true>
        {{-- vueで動的にプルダウンを作成 --}}
        <option v-for="item in selectGetItemSchedules.lessons" :value="item.id">
            @{{ item.value }}
        </option>
    </x-input.select>

    <div v-cloak>
        <x-bs.table vShow="form.schedule_id" :hover=false :vHeader=true>
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

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.card>
        <x-input.date-picker caption="振替日" id="target_date" :rules=$rules/>

        <x-input.select caption="時限" id="period_no"
            :select2=true :select2Search=false :blank=true>
            {{-- vueで動的にプルダウンを作成 --}}
            <option v-for="item in selectGetItemPeriods" :value="item.code">
                @{{ item.value }}
            </option>
        </x-input.select>

        <x-input.time-picker caption="開始時刻（変更する場合）" id="start_time" :rules=$rules />

    </x-bs.card>

    <x-input.select caption="講師名（変更する場合）" id="change_tid" :select2=true
        :select2=true :select2Search=true :blank=true>
        {{-- vueで動的にプルダウンを作成 --}}
        <option v-for="item in selectGetItem.tutors" :value="item.id">
            @{{ item.value }}
        </option>
    </x-input.select>

    <x-bs.callout title="登録の際の注意事項" type="warning">
        入力した振替授業のスケジュールが登録されます。<br>
        対象の生徒・講師へお知らせが通知されます。
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
