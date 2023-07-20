@extends('adminlte::page')

@section('title', '講師情報詳細')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- カード --}}
<x-bs.card :form="true">

    {{-- hidden 削除用--}}
    <x-input.hidden id="tid" :editData=$editData />

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
		<x-button.edit href="{{ route('tutor_mng-leave-edit', '101') }}" caption="退職処理" btn="btn-danger" icon="" :small=true />
        <x-button.edit href="{{ route('tutor_mng-edit', '101') }}" caption="更新" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        講師情報
    </x-slot>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">講師ID</th>
            <td>101</td>
        </tr>
        <tr>
            <th>講師名</th>
            <td>CWテスト教師１０１</td>
        </tr>
        <tr>
            <th>電話番号</th>
            <td>070-1111-2222</td>
        </tr>
        <tr>
            <th>メールアドレス</th>
            <td>teacher0101@mp-sample.rulez.jp</td>
        </tr>
        <tr>
            <th>生年月日</th>
            <td>2003/04/10</td>
        </tr>
        <tr>
            <th>学年</th>
            <td>大学１年</td>
        </tr>        <tr>
            <th>授業時給（ベース給）</th>
            <td>1300</td>
        </tr>
        <tr>
            <th>講師ステータス</th>
            <td>在籍</td>
        </tr>
        <tr>
            <th>勤務開始日</th>
            <td>2022/04/01</td>
        </tr>
        <tr>
            <th>退職日</th>
            <td></td>
        </tr>
        <tr>
            <th>勤務年数</th>
            <td>1年3ヶ月</td>
        </tr>
        <tr>
            <th>担当科目</th>
            <td>数学</td>
        </tr>
        <tr>
            <th>メモ</th>
            <td></td>
        </tr>
    </x-bs.table>
</x-bs.card>

<x-bs.card>
    <x-slot name="tools">
		<x-button.new href="{{ route('tutor_mng-campus-new', '101') }}" caption="新規登録" icon="" :small=true />
    </x-slot>

    <x-slot name="card_title">
        所属情報
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true class="inner-card">

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="20%">校舎</th>
            <th>交通費</th>
            <th width="10%"></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>久我山</td>
            <td>800</td>
            <td>
                <x-button.list-edit href="{{ route('tutor_mng-campus-edit', 1) }}"/>
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

@stop