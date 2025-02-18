@extends('adminlte::page')

@section('title', '追加請求申請編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の追加請求申請について、編集を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">講師名</th>
            <td>{{$editData['tutor_name']}}</td>
        </tr>
        <tr>
            <th>請求種別</th>
            <td>{{$editData['surcharge_kind_name']}}</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>{{$editData['campus_name']}}</td>
        </tr>
        <tr>
            <th>実施日</th>
            <td>{{$editData['working_date']->format('Y/m/d')}}</td>
        </tr>

        {{-- 開始時刻・時間はデータがあるときのみ表示 --}}
        @if(isset($editData['start_time']))
        <tr>
            <th>開始時刻</th>
            <td>
                {{$editData['start_time']->format('H:i')}}
            </td>
        </tr>
        <tr>
            <th>時間（分）</th>
            <td>{{$editData['minutes']}}</td>
        </tr>
        @endif

        <tr>
            <th>金額</th>
            <td>{{number_format($editData['tuition'])}}</td>
        </tr>
        <tr>
            <th>内容（作業・費目等）</th>
            <td class="nl2br">{{$editData['comment']}}</td>
        </tr>
        
        {{-- 承認者・承認日時はデータがあるときのみ表示 --}}
        @if(isset($editData['approval_user']))
        <tr>
            <th>承認者</th>
            <td>{{$editData['approval_user_name']}}</td>
        </tr>
        @endif
        @if(isset($editData['approval_time']))
        <tr>
            <th>承認日時</th>
            <td>{{$editData['approval_time']->format('Y/m/d H:i')}}</td>
        </tr>
        @endif
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select id="approval_status" caption="ステータス" :select2=true :mastrData=$approvalStatusList
        :editData=$editData :select2Search=false :blank=false />

    <x-input.textarea caption="管理者コメント" id="admin_comment" :rules=$rules :editData=$editData />

    <x-input.select id="payment_date" caption="支払年月" :select2=true :mastrData=$paymentDateList :editData=$paymentYm
        :select2Search=false :blank=false vShow="form.approval_status == {{ App\Consts\AppConst::CODE_MASTER_2_1 }}" />

    {{-- hidden --}}
    <x-input.hidden id="surcharge_id" :editData=$editData />
    <x-input.hidden id="working_date" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-edit />
        </div>
    </x-slot>

</x-bs.card>

@stop