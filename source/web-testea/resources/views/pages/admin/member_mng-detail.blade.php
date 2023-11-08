@extends('adminlte::page')

@section('title', '生徒カルテ')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card>
    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.edit href="{{ route('member_mng-leave-edit', $student->student_id) }}" caption="生徒退会" icon="" :small=true
            btn="btn-danger" disabled={{$disabled}} />
        <x-button.edit href="{{ route('member_mng-edit', $student->student_id) }}" caption="生徒情報編集" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        生徒情報
    </x-slot>

    <x-bs.table :hover=false :vHeader=true class="mb-4 fix">
        <tr>
            <th width="35%">生徒ID</th>
            <td>{{$student->student_id}}</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>{{$student->name}}</td>
        </tr>
        <tr>
            <th>生徒名かな</th>
            <td>{{$student->name_kana}}</td>
        </tr>
        <tr>
            <th>生徒電話番号</th>
            <td>{{$student->tel_stu}}</td>
        </tr>
        <tr>
            <th>保護者電話番号</th>
            <td>{{$student->tel_par}}</td>
        </tr>
        <tr>
            <th>生徒メールアドレス</th>
            <td><a href="mailto:{{$student->email_stu}}">{{$student->email_stu}}</a></td>
        </tr>
        <tr>
            <th>保護者メールアドレス</th>
            <td><a href="mailto:{{$student->email_par}}">{{$student->email_par}}</a></td>
        </tr>
        <tr>
            <th>生年月日</th>
            <td>{{$student->birth_date->format('Y/m/d')}}</td>
        </tr>
        <tr>
            <th>学年</th>
            <td>{{$student->grade_name}}</td>
        </tr>
        <tr>
            <th>所属校舎</th>
            <td>{{$campus_names}}</td>
        </tr>
        <tr>
            <th>所属学校（小）</th>
            <td>{{$student->school_e_name}}</td>
        </tr>
        <tr>
            <th>所属学校（中）</th>
            <td>{{$student->school_j_name}}</td>
        </tr>
        <tr>
            <th>所属学校（高）</th>
            <td>{{$student->school_h_name}}</td>
        </tr>
        <tr>
            <th>会員ステータス</th>
            <td>{{$student->status_name}}</td>
        </tr>
        <tr>
            <th>入会日</th>
            {{-- nullだとformatでエラーが出るためif文を追加した --}}
            <td>
                @if(isset($student->enter_date))
                {{$student->enter_date->format('Y/m/d')}}
                @endif
            </td>
        </tr>
        <tr>
            <th>退会日</th>
            <td>
                @if(isset($student->leave_date))
                {{$student->leave_date->format('Y/m/d')}}
                @endif
            </td>
        </tr>
        <tr>
            <th>通塾期間</th>
            <td>
                @if(isset($student->enter_date))
                {{floor($student->enter_term / 12)}}年{{floor($student->enter_term % 12)}}ヶ月
                @endif
            </td>
        </tr>
        <tr>
            <th>外部サービス顧客ID</th>
            <td>{{$student->lead_id}}</td>
        </tr>
        <tr>
            <th>ストレージURL</th>
            <td>
                <a href="{{$student->storage_link}}">{{$student->storage_link}}</a>
            </td>
        </tr>
        <tr>
            <th>メモ</th>
            <td>{{$student->memo}}</td>
        </tr>
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.new href="{{ route('record-new', $student->student_id) }}" caption="記録登録" :small=true />
        <x-button.edit href="{{ route('record', $student->student_id) }}" caption="記録管理" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        連絡記録
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">対応日時</th>
            <th>記録種別</th>
            <th>校舎</th>
            <th>担当者名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        {{-- 最新5件のみ表示 --}}
        @for ($i = 0; $i <5; $i++) {{-- 5件未満の場合はその時点で処理を抜ける --}} @if(empty($records[$i])) @break @endif <tr>
            <td>{{$records[$i]->received_date->format('Y/m/d')}} {{$records[$i]->received_time->format('H:i')}}</td>
            <td>{{$records[$i]->kind_name}}</td>
            <td>{{$records[$i]->campus_name}}</td>
            <td>{{$records[$i]->admin_name}}</td>
            @php
            $ids = ['id' => $records[$i]->record_id, 'sid' => $records[$i]->student_id,];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-record" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>

    {{-- 6件以上ある場合は残数表示する --}}
    @if(5 < count($records)) <div class="text-right">他 @php echo count($records)-5 @endphp 件</div>
        @endif

</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.new href="{{ route('transfer_check-new') }}" :small=true caption="振替授業登録" />
        <x-button.edit href="{{ route('member_mng-calendar', $student->student_id) }}" caption="カレンダー" icon=""
            :small=true />
        <x-button.edit href="{{ route('member_mng-invoice', $student->student_id) }}" caption="請求管理" icon=""
            :small=true />
    </x-slot>

    <x-slot name="card_title">
        授業情報
    </x-slot>

    <x-bs.form-title>レギュラー授業情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">曜日</th>
            <th width="10%">時限</th>
            <th width="10%">校舎</th>
            <th width="20%">コース名</th>
            <th width="20%">講師名</th>
            <th width="20%">科目</th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($regular_classes); $i++) <tr>
            <td>{{$regular_classes[$i]->day_name}}</td>
            <td>{{$regular_classes[$i]->period_no}}</td>
            <td>{{$regular_classes[$i]->campus_name}}</td>
            <td>{{$regular_classes[$i]->course_name}}</td>
            <td>{{$regular_classes[$i]->tutor_name}}</td>
            <td>{{$regular_classes[$i]->subject_name}}</td>
            </tr>
            @endfor
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-bs.form-title>未振替授業情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">授業日</th>
            <th class="t-minimum">時限</th>
            <th width="10%">校舎</th>
            <th width="15%">コース名</th>
            <th width="15%">講師名</th>
            <th width="15%">科目</th>
            <th width="10%">授業区分</th>
            <th width="15%">出欠ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($not_yet_transfer_classes); $i++) <tr>
            <td>{{$not_yet_transfer_classes[$i]->target_date->format('Y/m/d')}}</td>
            <td>{{$not_yet_transfer_classes[$i]->period_no}}</td>
            <td>{{$not_yet_transfer_classes[$i]->campus_name}}</td>
            <td>{{$not_yet_transfer_classes[$i]->course_name}}</td>
            <td>{{$not_yet_transfer_classes[$i]->tutor_name}}</td>
            <td>{{$not_yet_transfer_classes[$i]->subject_name}}</td>
            <td>{{$not_yet_transfer_classes[$i]->lesson_kind_name}}</td>
            <td>{{$not_yet_transfer_classes[$i]->absent_status_name}}</td>
            @php
            $ids = ['id' => $not_yet_transfer_classes[$i]->schedule_id, 'sid' =>
            $not_yet_transfer_classes[$i]->student_id,];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-room_calendar" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-bs.form-title>イレギュラー授業情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">授業日</th>
            <th class="t-minimum">時限</th>
            <th width="10%">校舎</th>
            <th width="15%">コース名</th>
            <th width="15%">講師名</th>
            <th width="15%">科目</th>
            <th width="10%">授業区分</th>
            <th width="15%">出欠ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i < count($irregular_classes); $i++) <tr>
            <td>{{$irregular_classes[$i]->target_date->format('Y/m/d')}}</td>
            <td>{{$irregular_classes[$i]->period_no}}</td>
            <td>{{$irregular_classes[$i]->campus_name}}</td>
            <td>{{$irregular_classes[$i]->course_name}}</td>
            <td>{{$irregular_classes[$i]->tutor_name}}</td>
            <td>{{$irregular_classes[$i]->subject_name}}</td>
            <td>{{$irregular_classes[$i]->lesson_kind_name}}</td>
            {{-- 受講生徒情報にデータがあればその出欠ステータスを表示 1対多 --}}
            @if(isset($irregular_classes[$i]->class_student_id))
            <td>{{$irregular_classes[$i]->class_absent_status_name}}</td>
            @else
            <td>{{$irregular_classes[$i]->absent_status_name}}</td>
            @endif
            @php
            // 1対多授業は受講生徒情報の生徒IDをセット、個別授業はスケジュール情報の生徒IDをセットする（モーダル選択時の閲覧ガード用）
            if(isset($irregular_classes[$i]->class_student_id)){
            $ids = ['id' => $irregular_classes[$i]->schedule_id, 'sid' => $irregular_classes[$i]->class_student_id];
            }else{
            $ids = ['id' => $irregular_classes[$i]->schedule_id, 'sid' => $irregular_classes[$i]->student_id];
            }
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-room_calendar" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.new href="{{ route('desired_mng-new', $student->student_id) }}" caption="受験校登録" :small=true />
        <x-button.edit href="{{ route('desired_mng', $student->student_id) }}" caption="受験校管理" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        受験校情報
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th class="t-minimum">受験年度</th>
            <th class="t-minimum">志望順</th>
            <th>受験校</th>
            <th>学部・学科名</th>
            <th>受験日程名</th>
            <th>受験日</th>
            <th>合否</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i <5; $i++) @if(empty($entrance_exams[$i])) @break @endif <tr>
            <td>{{$entrance_exams[$i]->exam_year}}</td>
            <td>{{$entrance_exams[$i]->priority_no}}</td>
            <td>{{$entrance_exams[$i]->school_name}}</td>
            <td>{{$entrance_exams[$i]->department_name}}</td>
            <td>{{$entrance_exams[$i]->exam_name}}</td>
            <td>{{$entrance_exams[$i]->exam_date->format('Y/m/d')}}</td>
            <td>{{$entrance_exams[$i]->result_name}}</td>
            @php
            $ids = ['id' => $entrance_exams[$i]->student_exam_id, 'sid' => $entrance_exams[$i]->student_id,];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-desired" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>

    @if(5 < count($entrance_exams)) <div class="text-right">現年度 他 @php echo count($entrance_exams)-5 @endphp 件</div>
        @endif

</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.new href="{{ route('grades_mng-new', $student->student_id) }}" caption="成績登録" :small=true />
        <x-button.edit href="{{ route('grades_mng', $student->student_id) }}" caption="成績管理" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        成績情報
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">登録日</th>
            <th width="15%">種別</th>
            <th>学期・試験名</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i <5; $i++) @if(empty($scores[$i])) @break @endif <tr>
            <td>{{$scores[$i]->regist_date->format('Y/m/d')}}</td>
            <td>{{$scores[$i]->exam_type_name}}</td>
            <td>{{$scores[$i]->practice_exam_name}} {{$scores[$i]->regular_exam_name}} {{$scores[$i]->term_name}}</td>
            @php
            $ids = ['id' => $scores[$i]->score_id, 'sid' => $scores[$i]->student_id,];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-grades_mng" :dataAttr="$ids" />
            </td>
            </tr>
            @endfor
    </x-bs.table>

    @if(5 < count($scores)) <div class="text-right">他 @php echo count($scores)-5 @endphp 件</div>
        @endif

</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.new href="{{ route('badge-new', $student->student_id) }}" caption="バッジ付与登録" :small=true />
        <x-button.edit href="{{ route('badge', $student->student_id) }}" caption="バッジ付与管理" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        バッジ付与情報
    </x-slot>

    {{-- テーブル --}}
    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th>バッジ数合計</th>
            <td>@php echo count($badges) @endphp</td>
        </tr>
    </x-bs.table>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">認定日</th>
            <th width="15%">校舎</th>
            <th width="15%">担当者名</th>
            <th>認定理由</th>
        </x-slot>

        {{-- テーブル行 --}}
        @for ($i = 0; $i <5; $i++) @if(empty($badges[$i])) @break @endif <tr>
            <td>{{$badges[$i]->authorization_date->format('Y/m/d')}}</td>
            <td>{{$badges[$i]->campus_name}}</td>
            <td>{{$badges[$i]->admin_name}}</td>
            <td>{{$badges[$i]->reason}}</td>
            </tr>
            @endfor
    </x-bs.table>

    @if(5 < count($badges)) <div class="text-right">他 @php echo count($badges)-5 @endphp 件</div>
        @endif

        {{-- フッター --}}
        <x-slot name="footer">
            <div class="d-flex justify-content-between">
                <x-button.back />
            </div>
        </x-slot>
</x-bs.card>

{{-- モーダル --}}
{{-- 受講情報 --}}
@include('pages.admin.modal.room_calendar-modal', ['modal_id' => 'modal-dtl-room_calendar'])
{{-- 生徒成績 --}}
@include('pages.admin.modal.grades_mng-modal', ['modal_id' => 'modal-dtl-grades_mng'])
{{-- 電話・面談記録 --}}
@include('pages.admin.modal.record-modal', ['modal_id' => 'modal-dtl-record'])
{{-- 受験校情報 --}}
@include('pages.admin.modal.desired_mng-modal', ['modal_id' => 'modal-dtl-desired'])

@stop