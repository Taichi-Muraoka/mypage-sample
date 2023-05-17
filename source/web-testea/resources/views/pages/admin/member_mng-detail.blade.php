@extends('adminlte::page')

@section('title', '生徒カルテ')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

<x-bs.card>
    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
		<x-button.edit href="{{ route('member_mng-edit', $student->sid) }}" caption="生徒情報編集" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        生徒情報
    </x-slot>

    <x-bs.table :hover=false :vHeader=true class="mb-4 fix">
        <tr>
            <th width="35%">生徒No</th>
            <td>{{$student->sid}}</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>{{$student->name}}</td>
        </tr>
        <tr>
            <th>メールアドレス</th>
            <td>{{$student->email}}</td>
        </tr>
        <tr>
            <th>学年</th>
            <td>{{$student->cls_name}}</td>
        </tr>
        <tr>
            <th>所属校舎</th>
            <td>{{$roomcds}}</td>
        </tr>
        <tr>
            <th>所属学校</th>
            <td>渋谷第一中学校</td>
        </tr>
        <tr>
            <th>会員ステータス</th>
            <td>入会</td>
        </tr>
        <tr>
            <th>入会日</th>
            <td>2020/04/01</td>
        </tr>
        <tr>
            <th>契約期間</th>
            <td>37 ヶ月</td>
        </tr>
        <tr>
            <th>バッジ数</th>
            <td>2</td>
        </tr>
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.edit href="{{ route('record', $student->sid) }}" caption="バッジ付与管理" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        バッジ付与情報
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">付与日</th>
            <th width="20%">校舎</th>
            <th width="20%">担当者名</th>
            <th>認定理由</th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/05/10</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>生徒紹介（佐藤次郎さん）</td>
        </tr>
        <tr>
            <td>2023/04/01</td>
            <td>久我山</td>
            <td>鈴木　花子</td>
            <td>契約期間が３年を超えた</td>
        </tr>
        </tr>
    </x-bs.table>

</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
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
        <x-button.edit href="{{ route('desired_mng', $student->sid) }}" caption="受験校管理" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        受験校情報
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="10%">志望順</th>
            <th>学校名</th>
            <th>学部・学科名</th>
            <th>受験日</th>
            <th>合否</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>1</td>
            <td>青山高等学校</td>
            <td>普通科</td>
            <td>2023/03/03</td>
            <td>合格</td>
            @php
            $ids = ['roomcd' => 110, 'seq' => 1, 'sid' => 1];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-regulation" :dataAttr="$ids" />
            </td>
        </tr>
            <td>2</td>
            <td>成城第二高等学校</td>
            <td>特進科</td>
            <td>2023/02/01</td>
            <td>合格</td>
            @php
            $ids = ['roomcd' => 110, 'seq' => 1, 'sid' => 1];
            @endphp
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-regulation" :dataAttr="$ids" />
            </td>
        </tr>
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.edit href="{{ route('agreement_mng', $student->sid) }}" caption="契約管理" icon="" :small=true />
        <x-button.edit href="{{ route('member_mng-invoice', $student->sid) }}" caption="請求管理" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        契約情報
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">開始日</th>
            <th width="15%">終了日</th>
            <th width="15%">月額</th>
            <th>契約情報</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <x-bs.td-sp caption="開始日">2022/04/01</x-bs.td-sp>
            <x-bs.td-sp caption="終了日">2023/03/31</x-bs.td-sp>
            <x-bs.td-sp caption="月額" class="t-price">16,390</x-bs.td-sp>
            <x-bs.td-sp caption="契約情報">月4回 60分 個別（中学1･2年生）料金</x-bs.td-sp>

            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-regulation" />
            </td>
        </tr>
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
        <x-button.edit href="{{ route('member_mng-calendar', $student->sid) }}" caption="カレンダー" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        受講情報
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">授業日</th>
            <th>時限</th>
            <th>校舎</th>
            <th>講師名</th>
            <th>コース名</th>
            <th>教科</th>
            <th>授業種別</th>
            <th>出欠ステータス</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/03/03</td>
            <td>6</td>
            <td>久我山</td>
            <td>CWテスト教師１０１</td>
            <td>個別指導コース</td>
            <td>数学</td>
            <td>初回授業（入会金無料）</td>
            <td>出席</td>
            <td>
                <x-button.list-dtl  dataTarget="#modal-dtl-student_class" />
            </td>
        </tr>
        <tr>
            <td>2023/03/10</td>
            <td>6</td>
            <td>久我山</td>
            <td>CWテスト教師１０１</td>
            <td>個別指導コース</td>
            <td>英語</td>
            <td>通常</td>
            <td>出席</td>
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-student_class" />
            </td>
        </tr>
        <tr>
            <td>2023/03/17</td>
            <td>6</td>
            <td>久我山</td>
            <td>CWテスト教師１０１</td>
            <td>個別指導コース</td>
            <td>英語</td>
            <td>通常</td>
            <td>後日振替（振替日未定）</td>
            <td>
                <x-button.list-dtl  dataTarget="#modal-dtl-student_class" />
            </td>
        </tr>
        <tr>
            <td>2023/03/24</td>
            <td>5</td>
            <td>久我山</td>
            <td>CWテスト教師１０２</td>
            <td>個別指導コース</td>
            <td>数学</td>
            <td>通常</td>
            <td>実施前</td>
            <td>
                <x-button.list-dtl  dataTarget="#modal-dtl-student_class" />
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
            <th width="15%">試験種別</th>
            <th>試験名</th>
            <th>合計点</th>
            <th>偏差値</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/03/18</td>
            <td>模擬試験</td>
            <td>全国統一模試</td>
            <td>391</td>
            <td>62</td>
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-grades_mng" />
            </td>
        </tr>
        <tr>
            <td>2023/02/28</td>
            <td>定期考査</td>
            <td>学年末考査</td>
            <td>380</td>
            <td></td>
            <td>
                <x-button.list-dtl dataTarget="#modal-dtl-grades_mng" />
            </td>
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
{{-- 規定情報 --}}
@include('pages.admin.modal.member_mng_regulation-modal', ['modal_id' => 'modal-dtl-regulation'])
{{-- 受講情報 --}}
@include('pages.admin.modal.student_class-modal', ['modal_id' => 'modal-dtl-student_class'])
{{-- 生徒成績 --}}
@include('pages.admin.modal.grades_mng-modal', ['modal_id' => 'modal-dtl-grades_mng'])
{{-- 電話・面談記録 --}}
@include('pages.admin.modal.record-modal', ['modal_id' => 'modal-dtl-record'])

@stop