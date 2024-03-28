@extends('adminlte::page')
@inject('formatter','App\Libs\CommonDateFormat')

@section('title', '振替情報編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    <p>以下の振替調整依頼について、振替または代講スケジュールを登録します。</p>
    <p>（管理者が振替を承認しない場合や、生徒－講師間で調整が難しい場合）</p>

    {{-- hidden --}}
    <x-input.hidden id="campus_cd" :editData=$editData />
    <x-input.hidden id="transfer_apply_id" :editData=$editData />
    <x-input.hidden id="period_no_bef" />

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">申請者種別</th>
            <td>生徒</td>
        </tr>
        <tr>
            <th>授業日・時限</th>
            <td>{{$editData['target_date']}} {{$editData['period_no']}}限</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>{{$editData['campus_name']}}</td>
        </tr>
        <tr>
            <th>コース</th>
            <td>{{$editData['course_name']}}</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>{{$editData['student_name']}}</td>
        </tr>
        <tr>
            <th>講師名</th>
            <td>{{$editData['tutor_name']}}</td>
        </tr>
        <tr>
            <th>教科</th>
            <td>{{$editData['subject_name']}}</td>
        </tr>
        <tr>
            <th>振替理由／連絡事項など</th>
            <td class="nl2br">{{$editData['transfer_reason']}}</td>
        </tr>
        <tr>
            <th>ステータス</th>
            <td>{{$editData['approval_status_name']}}</td>
        </tr>
    </x-bs.table>

    <div class="card-body px-0">

        {{-- テーブル --}}
        <x-bs.table :hover=false>
            <x-slot name="thead">
                <th class="t-minimum">希望順</th>
                <th>振替希望日</th>
                <th>時限</th>
                <th> </th>
            </x-slot>

            {{-- テーブル行 --}}
            @for ($i = 1; $i <= 3; $i++)
                @if ($editData['transfer_date_' . $i] !='' )
                    @if (!$editData['free_check_' . $i] || $editData['free_check_' . $i]=='' )
                    <tr v-cloak>
                    @else
                    <tr v-cloak style="background-color: #C0C0C0">
                    @endif
                        <td>{{$i}}</td>
                        <td>{{$editData['transfer_date_' . $i]}}</td>
                        <td>{{$editData['period_no_' . $i]}}</td>
                        <td>{{$editData['free_check_' . $i]}}</td>
                    </tr>
                @endif
            @endfor
        </x-bs.table>
    </div>

    <p class="input-title">振替授業登録</p>
    <x-bs.card>
        {{-- 振替授業・代講授業の選択 --}}
        <x-bs.form-group>
            <x-input.radio caption="振替授業" id="transfer_kind-1" name="transfer_kind" value="{{ App\Consts\AppConst::CODE_MASTER_54_1 }}" :checked=true :editData=$editData />
            <x-input.radio caption="代講授業（スケジュール変更なし）" id="transfer_kind-2" name="transfer_kind" value="{{ App\Consts\AppConst::CODE_MASTER_54_2 }}" :editData=$editData />
        </x-bs.form-group>
        {{-- 余白 --}}
        <div class="mb-3"></div>

        {{-- 振替授業の場合 --}}
        <x-input.date-picker caption="振替日" id="target_date" vShow="form.transfer_kind == {{ App\Consts\AppConst::CODE_MASTER_54_1 }}" :rules=$rules/>

        <x-input.select vShow="form.transfer_kind == {{ App\Consts\AppConst::CODE_MASTER_54_1 }}" caption="時限" id="period_no" :rules=$rules
            :select2=true :editData=$editData :select2Search=false :blank=true>
            {{-- vueで動的にプルダウンを作成 --}}
            <option v-for="item in selectGetItemPeriods" :value="item.code">
                @{{ item.value }}
            </option>
        </x-input.select>

        <x-input.time-picker caption="開始時刻（変更する場合）" id="start_time" :rules=$rules vShow="form.transfer_kind == {{ App\Consts\AppConst::CODE_MASTER_54_1 }}"/>

        <x-input.select caption="講師名（変更する場合）" id="change_tid" :select2=true :mastrData=$tutors :editData="$editData"
            vShow="form.transfer_kind == {{ App\Consts\AppConst::CODE_MASTER_54_1 }}" :select2Search=true :blank=true />

        {{-- 代講授業の場合 --}}
        <x-input.select caption="代講講師名" id="substitute_tid" :select2=true :mastrData=$tutors :editData="$editData"
            vShow="form.transfer_kind == {{ App\Consts\AppConst::CODE_MASTER_54_2 }}" :select2Search=true :blank=true />

    </x-bs.card>

    <x-input.textarea caption="承認者コメント" id="comment" :editData=$editData :rules=$rules />

    {{-- スケジュール登録のバリデーションエラー時のメッセージ --}}
    <x-bs.form-group name="validate_schedule" />

    <x-bs.callout title="登録の際の注意事項" type="warning">
        ・「振替授業登録」欄に入力した振替授業または代講授業のスケジュールが登録されます。<br>
        &emsp;対象の生徒・講師へお知らせが通知されます。<br>
        ・削除ボタン押下により、振替依頼の取消し・キャンセルが行われます。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete-validation />
                <x-button.submit-edit />
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop
