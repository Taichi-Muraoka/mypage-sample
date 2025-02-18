@extends('adminlte::page')

@section('title', '講師情報詳細')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- カード --}}
<x-bs.card>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.edit href="{{ route('tutor_mng-leave-edit', $tutor->tutor_id) }}" caption="退職処理" icon="" :small=true
            btn="btn-danger" disabled={{$disabledLeaveBtn}} />
        <x-button.edit href="{{ route('tutor_mng-edit', $tutor->tutor_id) }}" caption="更新" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        講師情報
    </x-slot>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">講師ID</th>
            <td>{{$tutor->tutor_id}}</td>
        </tr>
        <tr>
            <th>講師名</th>
            <td>{{$tutor->name}}</td>
        </tr>
        <tr>
            <th>講師名かな</th>
            <td>{{$tutor->name_kana}}</td>
        </tr>
        <tr>
            <th>電話番号</th>
            <td>{{$tutor->tel}}</td>
        </tr>
        <tr>
            <th>メールアドレス</th>
            <td><a href="mailto:{{$tutor->email}}">{{$tutor->email}}</a></td>
        </tr>
        <tr>
            <th>住所</th>
            <td>{{$tutor->address}}</td>
        </tr>
        <tr>
            <th>生年月日</th>
            <td>{{$tutor->birth_date->format('Y/m/d')}}</td>
        </tr>
        <tr>
            <th>性別</th>
            <td>{{$tutor->gender_name}}</td>
        </tr>
        <tr>
            <th>学年</th>
            <td>{{$tutor->grade_name}}</td>
        </tr>
        <tr>
            <th>所属大学</th>
            <td>{{$tutor->school_u_name}}</td>
        </tr>
        <tr>
            <th>出身高校</th>
            <td>{{$tutor->school_h_name}}</td>
        </tr>
        <tr>
            <th>出身中学</th>
            <td>{{$tutor->school_j_name}}</td>
        </tr>
        <tr>
            <th>授業時給（ベース給）</th>
            <td>{{$tutor->hourly_base_wage}}</td>
        </tr>
        <tr>
            <th>講師ステータス</th>
            <td>{{$tutor->status_name}}</td>
        </tr>
        <tr>
            <th>勤務開始日</th>
            {{-- nullだとformatでエラーが出るためif文を追加した --}}
            <td>
                @if(isset($tutor->enter_date))
                {{$tutor->enter_date->format('Y/m/d')}}
                @endif
            </td>
        </tr>
        <tr>
            <th>退職日</th>
            <td>
                @if(isset($tutor->leave_date))
                {{$tutor->leave_date->format('Y/m/d')}}
                @endif
            </td>
        </tr>
        <tr>
            <th>勤務年数</th>
            <td>
                @if(isset($tutor->enter_term))
                {{floor($tutor->enter_term / 12)}}年{{floor($tutor->enter_term % 12)}}ヶ月
                @endif
            </td>
        </tr>
        <tr>
            <th>担当教科</th>
            <td>{{$subject_names}}</td>
        </tr>
        <tr>
            <th>メモ</th>
            <td class="nl2br">{{$tutor->memo}}</td>
        </tr>
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    {{-- 新規登録は本部のみ可能 --}}
    @can('allAdmin')
    <x-slot name="tools">
        <x-button.new href="{{ route('tutor_mng-campus-new', $tutor->tutor_id) }}" caption="新規登録" icon="" :small=true
            disabled={{$disabledNewBtn}} />
    </x-slot>
    @endcan

    <x-slot name="card_title">
        所属情報
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">校舎</th>
            <th>交通費(往復)</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($campuses); $i++)
        <tr>
            <td>{{$campuses[$i]->campus_name}}</td>
            <td>{{$campuses[$i]->travel_cost}}</td>
            <td>
                <x-button.list-edit href="{{ route('tutor_mng-campus-edit', $campuses[$i]->tutor_campus_id) }}"
                    disabled="{{$campuses[$i]->disabled_btn}}" />
            </td>
        </tr>
        @endfor
    </x-bs.table>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
        </div>
    </x-slot>
</x-bs.card>

@stop