@extends('pages.common.modal')

@section('modal-body')

<x-bs.table :hover=false :vHeader=true>
    <tr>
        <th width="35%">登録日</th>
        <td>@2023/03/18</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>試験種別</th>
        <td>模擬試験</td>
    </tr>
    <tr>
        <th>試験名</th>
        <td>全国統一模試</td>
    </tr>
    <tr>
        <th colspan="2">試験成績</th>
    </tr>
    <tr>
        <td colspan="2">

            {{-- tableの中にtableを書くと線が出てしまう noborder-only-topを指定した --}}
            <x-bs.table :bordered=false :hover=false class="noborder-only-top">
                <x-slot name="thead">
                    <th width="20%">教科</th>
                    <th width="20%">得点</th>
                    <th width="20%">前回比</th>
                    <th width="20%">学年平均</th>
                    <th width="20%">偏差値</th>
                </x-slot>

                <tr>
                    <x-bs.td-sp>国語</x-bs.td-sp>
                    <x-bs.td-sp>80点</x-bs.td-sp>
                    <x-bs.td-sp>↑</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>国語</x-bs.td-sp>
                    <x-bs.td-sp>80点</x-bs.td-sp>
                    <x-bs.td-sp>↑</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>数学</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp>↑</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>理科</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp>↑</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>社会</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp>↑</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>英語</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp>↑</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>

            </x-bs.table>
        </td>
    </tr>

    <tr>
        <th colspan="2">次回の試験に向けての抱負</th>
    </tr>
    {{-- nl2br: 改行 --}}
    <td colspan="2" class="nl2br">次回もがんばります</td>
</x-bs.table>

@overwrite