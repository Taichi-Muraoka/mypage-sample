@extends('adminlte::page')

@section('title', '生徒カルテ')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card>
    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
		<x-button.edit href="{{ route('member_mng-leave-edit', $student->sid) }}" caption="生徒退会" btn="btn-danger" icon="" :small=true />
		<x-button.edit href="{{ route('member_mng-edit', $student->sid) }}" caption="生徒情報編集" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        生徒情報
    </x-slot>

    <x-bs.table :hover=false :vHeader=true class="mb-4 fix">
        <tr>
            <th width="35%">生徒ID</th>
            <td>{{$student->sid}}</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>{{$student->name}}</td>
        </tr>
        <tr>
            <th>生徒名カナ</th>
            <td>テストセイト</td>
        </tr>
        <tr>
            <th>生徒電話番号</th>
            <td>08011112222</td>
        </tr>
        <tr>
            <th>保護者電話番号</th>
            <td>08033334444</td>
        </tr>
        <tr>
            <th>生徒メールアドレス</th>
            <td>{{$student->email}}</td>
        </tr>
        <tr>
            <th>保護者メールアドレス</th>
            <td>parent0001@ap.jeez.jp</td>
        </tr>
        <tr>
            <th>学年</th>
            <td>{{$student->cls_name}}</td>
        </tr>
        <tr>
            <th>所属校舎</th>
            <td>久我山 日吉</td>
        </tr>
        <tr>
            <th>所属学校（小）</th>
            <td>千駄谷小学校</td>
        </tr>
        <tr>
            <th>所属学校（中）</th>
            <td>渋谷第一中学校</td>
        </tr>
        <tr>
            <th>所属学校（高）</th>
            <td></td>
        </tr>
        <tr>
            <th>会員ステータス</th>
            <td>在籍</td>
        </tr>
        <tr>
            <th>入会日</th>
            <td>2020/04/01</td>
        </tr>
        <tr>
            <th>ストレージURL</th>
            <td>
                <a href="https://drive.google.com/drive/folders/1GiSWPRMHYohxQ04OujILYQvjBtciXLiC?usp=drive_link">https://drive.google.com/drive/folders/1GiSWPRMHYohxQ04OujILYQvjBtciXLiC?usp=drive_link</a>
            </td>
        </tr>
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.new href="{{ route('desired_mng-new', $student->sid) }}" caption="受験校登録" :small=true />
        <x-button.edit href="{{ route('desired_mng', $student->sid) }}" caption="受験校管理" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        受験校情報
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">受験年度</th>
            <th width="10%">志望順</th>
            <th>学校名</th>
            <th>学部・学科名</th>
            <th>受験日程名</th>
            <th>受験日</th>
            <th>合否</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2022</td>
            <td>1</td>
            <td>青山高等学校</td>
            <td>普通科</td>
            <td>A日程</td>
            <td>2023/03/03</td>
            <td>合格</td>
            @php
            $ids = ['roomcd' => 110, 'seq' => 1, 'sid' => 1];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-desired" :dataAttr="$ids" />
            </td>
        </tr>
        <tr>
            <td>2022</td>
            <td>2</td>
            <td>成城第二高等学校</td>
            <td>特進科</td>
            <td>B日程</td>
            <td>2023/02/01</td>
            <td>合格</td>
            @php
            $ids = ['roomcd' => 110, 'seq' => 1, 'sid' => 1];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-desired" :dataAttr="$ids" />
            </td>
        </tr>
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.new href="{{ route('transfer_check-new') }}" :small=true caption="振替授業登録" />
        <x-button.edit href="{{ route('member_mng-calendar', $student->sid) }}" caption="カレンダー" icon="" :small=true />
        <x-button.edit href="{{ route('member_mng-invoice', $student->sid) }}" caption="請求管理" icon="" :small=true />
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
        <tr>
            <td>火</td>
            <td>6</td>
            <td>久我山</td>
            <td>個別指導コース</td>
            <td>CWテスト講師１０１</td>
            <td>英語</td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-bs.form-title>未振替授業情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>授業日</th>
            <th>時限</th>
            <th>校舎</th>
            <th>コース名</th>
            <th>講師名</th>
            <th>科目</th>
            <th>授業区分</th>
            <th>出欠ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/30</td>
            <td>6</td>
            <td>久我山</td>
            <td>個別指導コース</td>
            <td>CWテスト講師１０１</td>
            <td>英語</td>
            <td>通常</td>
            <td>振替中（未振替）</td>
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-student_class" />
            </td>
        </tr>
    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-bs.form-title>イレギュラー授業情報</x-bs.form-title>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>授業日</th>
            <th>時限</th>
            <th>校舎</th>
            <th>コース名</th>
            <th>講師名</th>
            <th>科目</th>
            <th>授業区分</th>
            <th>出欠ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/03/10</td>
            <td>6</td>
            <td>久我山</td>
            <td>個別指導コース</td>
            <td>CWテスト教師１０１</td>
            <td>英語</td>
            <td>追加</td>
            <td>実施前・出席</td>
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-student_class" />
            </td>
        </tr>
        <tr>
            <td>2023/03/17</td>
            <td>6</td>
            <td>久我山</td>
            <td>個別指導コース</td>
            <td>CWテスト教師１０１</td>
            <td>英語</td>
            <td>振替</td>
            <td>実施前・出席</td>
            <td>
                <x-button.list-dtl  dataTarget="#modal-dtl-student_class" />
            </td>
        </tr>
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.new href="{{ route('record-new', $student->sid) }}" caption="記録登録" :small=true />
        <x-button.edit href="{{ route('record', $student->sid) }}" caption="記録管理" icon="" :small=true />
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
        <tr>
            <td>2023/01/10 17:00</td>
            <td>面談記録</td>
            <td>久我山</td>
            <td>山田　太郎</td>
            <td>
                <x-button.list-dtl  dataTarget="#modal-dtl-record" />
            </td>
        <tr>
            <td>2023/01/09 19:30</td>
            <td>電話記録</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>
                <x-button.list-dtl  dataTarget="#modal-dtl-record" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.edit href="{{ route('grades_mng', $student->sid) }}" caption="成績管理" icon="" :small=true />
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
        <tr>
            <td>2023/03/18</td>
            <td>模擬試験</td>
            <td>全国統一模試</td>
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-grades_mng" :dataAttr="['id' => '1']" />
            </td>
        </tr>
        <tr>
            <td>2023/02/28</td>
            <td>定期考査</td>
            <td>学年末考査</td>
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-grades_mng" :dataAttr="['id' => '2']" />
            </td>
        </tr>
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.new href="{{ route('badge-new', $student->sid) }}" caption="バッジ付与登録" :small=true />
        <x-button.edit href="{{ route('badge', $student->sid) }}" caption="バッジ付与管理" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        バッジ付与情報
    </x-slot>

    {{-- テーブル --}}
    {{-- 詳細を表示 --}}
    <x-bs.table :hover=false :vHeader=true class="mb-4">
        <tr>
            <th width="15%">通塾バッジ数</th>
            <td width="18%" class="t-price">1</td>
            <th width="15%">成績バッジ数</th>
            <td width="18%" class="t-price">1</td>
            <th width="15%">紹介バッジ数</th>
            <td width="18%" class="t-price">2</td>
        </tr>
    </x-bs.table>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">認定日</th>
            <th width="10%">バッジ種別</th>
            <th width="10%">校舎</th>
            <th width="15%">担当者名</th>
            <th>認定理由</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/05/10</td>
            <td>紹介</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>生徒紹介（佐藤次郎さん）</td>
        </tr>
        <tr>
            <td>2023/04/01</td>
            <td>通塾</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>契約期間が３年を超えた</td>
        </tr>
        <tr>
            <td>2022/03/20</td>
            <td>紹介</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>生徒紹介（仙台太郎さん）</td>
        </tr>
        <tr>
            <td>2022/02/20</td>
            <td>成績</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>成績UP</td>
        </tr>
    </x-bs.table>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
        </div>
    </x-slot>
</x-bs.card>

{{-- モーダル --}}
{{-- 受講情報 --}}
@include('pages.admin.modal.student_class-modal', ['modal_id' => 'modal-dtl-student_class'])
{{-- 生徒成績 --}}
@include('pages.admin.modal.grades_mng-modal', ['modal_id' => 'modal-dtl-grades_mng'])
{{-- 電話・面談記録 --}}
@include('pages.admin.modal.record-modal', ['modal_id' => 'modal-dtl-record'])
{{-- 受験校情報 --}}
@include('pages.admin.modal.desired_mng-modal', ['modal_id' => 'modal-dtl-desired'])

@stop