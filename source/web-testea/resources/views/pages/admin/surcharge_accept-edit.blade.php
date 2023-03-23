@extends('adminlte::page')

@section('title', '追加請求申請承認')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の追加請求申請について、承認を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th>講師名</th>
            <td>CWテスト教師１０１</td>
        </tr>
        <tr>
            <th width="35%">請求種別</th>
            <td>事務作業</td>
        </tr>

        {{-- 種別：事務作業の場合 --}}
        <tr>
            <th>校舎</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th>実施日</th>
            <td>2023/03/20</td>
        </tr>
        <tr>
            <th>開始時刻</th>
            <td>16:00</td>
        </tr>
        <tr>
            <th>時間（分）</th>
            <td>60</td>
        </tr>
        <tr>
            <th>作業内容</th>
            <td>教材プリントコピー作業</td>
        </tr>

        {{-- 種別：その他作業の場合 --}}
        {{-- <tr>
            <th>費目</th>
            <td>テキスト購入</td>
        </tr>
        <tr>
            <th>金額</th>
            <td>1000</td>
        </tr> --}}

    </x-bs.table>

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select caption="ステータス" id="status" :select2=true :editData="$editData">
        <option value="1">承認待ち</option>
        <option value="2">承認</option>
        <option value="3">差戻し</option>
    </x-input.select>

    <x-input.textarea caption="事務局コメント" id="text" :rules=$rules />

    <x-input.select caption="支払い年月" id="payment" :select2=true :editData="$editData">
        <option value="1">2023/04</option>
        <option value="2">2023/05</option>
        <option value="3">2023/06</option>
    </x-input.select>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />
            <x-button.submit-edit />
        </div>
    </x-slot>

</x-bs.card>

@stop