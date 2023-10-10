@extends('adminlte::page')

@section('title', '教室カレンダー')

@section('content')

{{-- IDはローディング用 --}}
<x-bs.card :p0=true id="card-calendar">

    <x-bs.card :form=true>
        <x-bs.row>
            <x-bs.col2>
                @can('roomAdmin')
                {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
                    <x-input.select id="roomcd" :select2=true :select2Search=false :blank=false :editData=$editData>
                        <option value="110">久我山</option>
                    </x-input.select>
                @else
                    <x-input.select id="roomcd" :select2=true onChange="selectChangeRoom()" :select2Search=false :blank=false :editData=$editData>
                        <option value="110">久我山</option>
                        <option value="120">西永福</option>
                        <option value="130">本郷</option>
                    </x-input.select>
                @endcan
            </x-bs.col2>
        </x-bs.row>
        {{-- hidden カレンダー用--}}
        <x-input.hidden id="curDate" />
    </x-bs.card>

    <div id="calendar"></div>

</x-bs.card>

{{-- モーダル(スケジュール詳細モーダル) --}}
{{-- @include('pages.admin.modal.room_calendar-modal') --}}

@stop