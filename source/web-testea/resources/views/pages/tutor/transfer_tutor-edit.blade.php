@extends('adminlte::page')

@section('title','振替依頼承認')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業振替依頼について、振替希望日一覧から１つ選択し、承認を行います。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">授業日・時限</th>
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
            <th>教科</th>
            <td>{{$editData['subject_name']}}</td>
        </tr>
        <tr>
            <th>振替理由／連絡事項など</th>
            <td class="nl2br">{{$editData['transfer_reason']}}</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <div class="card-body px-0">
        <p class="input-title">振替希望日一覧</p>
        {{-- テーブル --}}
        <x-bs.table :hover=true :smartPhone=true>
            <x-slot name="thead">
                <th class="t-minimum">選択</th>
                <th>希望順</th>
                <th>振替希望日</th>
                <th>時限</th>
                <th> </th>
            </x-slot>

            {{-- テーブル行 --}}
            @for ($i = 1; $i <= 3; $i++) @if ($editData['transfer_date_' . $i] !='' ) @if(!$editData['free_check_' . $i]
                || $editData['free_check_' . $i]=='' ) <tr v-cloak>
                <x-bs.td-sp caption="選択">
                    <x-input.radio id="preferred{{$i}}" name="transfer_date_id" icheck=true
                        value="{{$editData['transfer_date_id_' . $i]}}" :icheck=false />
                </x-bs.td-sp>
                @else
                <tr v-cloak style="background-color: #C0C0C0">
                    <x-bs.td-sp caption="選択"> </x-bs.td-sp>
                    @endif
                    <x-bs.td-sp caption="希望順">{{$i}}</x-bs.td-sp>
                    <x-bs.td-sp caption="振替希望日">{{$editData['transfer_date_' . $i]}}</x-bs.td-sp>
                    <x-bs.td-sp caption="時限">{{$editData['period_no_' . $i]}}</x-bs.td-sp>
                    <x-bs.td-sp caption="">{{$editData['free_check_' . $i]}}</x-bs.td-sp>
                </tr>
                @endif
                @endfor
        </x-bs.table>
    </div>

    <x-input.select id="approval_status" caption="ステータス" :select2=true :mastrData=$statusList :editData=$editData
        :select2Search=false :blank=false />

    <x-input.textarea caption="コメント" id="comment" :rules=$rules :editData=$editData />

    <x-bs.callout title="登録の際の注意事項" type="warning">
        振替希望日のいずれも都合が合わない場合は、コメント欄に理由を入力し、
        ステータスを「差戻し（日程不都合）」または「差戻し（代講希望）」として送信してください。<br>
        ステータスを「承認」として送信すると、選択した振替日時で授業スケジュールが登録されます。
    </x-bs.callout>

    {{-- hidden --}}
    <x-input.hidden id="transfer_apply_id" :editData=$editData />
    <x-input.hidden id="tutor_id" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-approval caption="送信" />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop