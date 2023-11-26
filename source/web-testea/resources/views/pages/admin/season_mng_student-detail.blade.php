@extends('adminlte::page')

@section('title', '特別期間講習 生徒日程詳細')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- フォーム --}}
<x-bs.card :form=true>
    <x-slot name="card_title">
        {{$seasonStudent->student_name}}
    </x-slot>

    <p>生徒の連絡内容を確認し、教科毎にコマ組みを行います。<br>
    コマ組み画面で講習スケジュールを登録し、確認の上、コマ組み状態を登録してください。</p>
    ※生徒登録期間外の場合、コマ組み画面の表示はできません。</p>

    {{-- テーブル --}}
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">特別期間名</th>
            <td>{{$seasonStudent->year}}年{{$seasonStudent->season_name}}</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>{{$seasonStudent->campus_name}}</td>
        </tr>
        <tr>
            <th>生徒コメント</th>
            {{-- nl2br: 改行 --}}
            <td class="nl2br">{{$seasonStudent->comment}}</td>
        </tr>
    </x-bs.table>
    {{-- hidden 退避用--}}
    <x-input.hidden id="campus_cd" :editData=$seasonStudent />
    <x-input.hidden id="season_student_id" :editData=$seasonStudent />
    <x-input.hidden id="season_cd" :editData=$seasonStudent />

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.form-title>受講希望教科・受講回数</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table class="mb-3" :button=true>
        <x-slot name="thead">
            <th width="30%">教科</th>
            <th>教科別回数</th>
            <th>登録済スケジュール数</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @foreach ($subjectTimesList as $subjectTimes) <tr>
            <td>{{$subjectTimes->subject_name}}</td>
            <td>{{$subjectTimes->times}}</td>
            <td>{{$subjectTimes->count}}</td>
            <td>
                <x-button.list-edit href="{{ route('season_mng_student-plan', [$seasonStudent->season_student_id, $subjectTimes->subject_cd]) }}"
                    caption="コマ組み" :disabled=$planBtnDisabled/>
            </td>
        </tr>
        @endforeach
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    @if (count($schedules) > 0)
    <x-bs.form-title>登録済みの講習スケジュール一覧</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table class="mb-3">
        <x-slot name="thead">
            <th width="20%">受講日</th>
            <th>時限</th>
            <th>担当講師</th>
            <th>教科</th>
            <th>仮登録状態</th>
        </x-slot>

    {{-- テーブル行 --}}
        @foreach ($schedules as $schedule) <tr>
            <td>{{$schedule->target_date->format('Y/m/d')}}</td>
            <td>{{$schedule->period_no}}</td>
            <td>{{$schedule->tutor_name}}</td>
            <td>{{$schedule->subject_name}}</td>
            <td>{{$schedule->tentative_status_name}}</td>
        </tr>
        @endforeach
    </x-bs.table>
    @endif

    {{-- 余白 --}}
    <div class="mb-4"></div>

    <x-input.select id="plan_status" caption="コマ組み状態" :select2=true :mastrData=$planStatusList :editData=$editData
        :rules=$rules :select2Search=false :blank=false />

    {{-- 余白 --}}
    <div class="mb-5"></div>

    <x-bs.callout type="warning">
        全ての受講希望教科についてコマ組みを行った後、コマ組み状態を「対応済」としてください。<br>
        当機能でコマ組みされた講習スケジュールは仮確定の状態であり、生徒・講師への公開はされません。<br>
        管理者からは教室カレンダーより確認・編集を行うことができます。<br>
        スケジュールを確定し、生徒・講師へ公開するには、対象生徒全員のスケジュールを登録後にコマ組み確定処理を行ってください。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-edit caption="送信" />
        </div>
    </x-slot>

</x-bs.card>

@stop
