@extends('adminlte::page')

@section('title', 'お知らせ情報詳細')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    {{-- モック用 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4 fix">
        <tr>
            <th width="35%">通知日</th>
            <td>2023/06/16</td>
        </tr>
        <tr>
            <th>タイトル</th>
            <td>面談のご案内</td>
        </tr>
        <tr>
            <th>送信元校舎</th>
            <td>本部</td>
        </tr>
        <tr>
            <th>送信者</th>
            <td>本部管理者</td>
        </tr>
        <tr>
            <th>内容</th>
            {{-- nl2br: 改行 --}}
            <td class="nl2br">以下の日程で面談を実施します。
                7月1日～7月7日<br>都合の良い日をマイページからご連絡ください。</td>
        </tr>
        <tr>
            <th>お知らせ種別</th>
            <td>面談</td>
        </tr>
        <tr>
            <th>宛先種別</th>
            <td>グルーブ一斉</td>
        </tr>
        <tr>
            <th>宛先</th>
            <td>中1<br>中2<br>中3</td>
        </tr>
    </x-bs.table>

    {{-- 本番用 --}}
    {{-- <x-bs.table :hover=false :vHeader=true class="mb-4 fix">
        <tr>
            <th width="35%">通知日</th>
            <td>{{$notice->regist_time->format('Y/m/d')}}</td>
        </tr>
        <tr>
            <th>タイトル</th>
            <td>{{$notice->title}}</td>
        </tr>
        <tr>
            <th>送信元校舎</th>
            <td>{{$notice->room_name}}</td>
        </tr>
        <tr>
            <th>送信者</th>
            <td>{{$notice->sender}}</td>
        </tr>
        <tr>
            <th>内容</th>

            <td class="nl2br">{{$notice->text}}</td>
        </tr>
        @if($notice->notice_type == App\Consts\AppConst::CODE_MASTER_14_1 || $notice->notice_type == App\Consts\AppConst::CODE_MASTER_14_2)

        <tr>
            <th>模試・イベント情報</th>
            <td>{{$notice->tm_event_name}}（{{$notice->tm_event_date}}）</td>
        </tr>
        @endif
        <tr>
            <th>お知らせ種別</th>
            <td></td>
        </tr>
        <tr>
            <th>宛先種別</th>
            <td>{{$notice->type_name}}</td>
        </tr>
        <tr>
            <th>宛先</th>
            <td>
                @for ($i = 0; $i < count($destination_names); $i++)
                    @if ($destination_names[0]->student_name != null)
                        {{$destination_names[0]->student_name}}
                        @break
                    @elseif ($destination_names[0]->teacher_name != null)
                        {{$destination_names[0]->teacher_name}}
                        @break
                    @elseif ($destination_names[$i]->group_name != null)
                        {{$destination_names[$i]->group_name}}<br>
                    @endif
                @endfor
            </td>
        </tr>
    </x-bs.table> --}}

    {{-- hidden --}}
    <x-input.hidden id="noticeId" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-delete />
        </div>
    </x-slot>

</x-bs.card>

@stop