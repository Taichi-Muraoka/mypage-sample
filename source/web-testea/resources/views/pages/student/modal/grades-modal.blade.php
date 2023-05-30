@extends('pages.common.modal')

@section('modal-body')

{{-- 模試 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.id == 2">
    <tr>
        <th width="35%">登録日</th>
        <td>2023/03/28</td>
    </tr>
    <tr>
        <th>種別</th>
        <td>模試</td>
    </tr>
    <tr>
        <th>試験名</th>
        <td>全国統一模試</td>
    </tr>

    <tr>
        <th colspan="2">成績</th>
    </tr>
    <tr>
        <td colspan="2">
            {{-- tableの中にtableを書くと線が出てしまう noborder-only-topを指定した --}}
            <x-bs.table :bordered=false :hover=false class="noborder-only-top">
                <x-slot name="thead">
                    <th width="25%">教科</th>
                    <th width="25%">得点</th>
                    <th width="25%">学年平均</th>
                    <th width="25%">偏差値</th>
                </x-slot>

                <tr>
                    <x-bs.td-sp>全教科合計</x-bs.td-sp>
                    <x-bs.td-sp>380点</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>国語</x-bs.td-sp>
                    <x-bs.td-sp>80点</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>数学</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>理科</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>社会</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp></x-bs.td-sp>
                    <x-bs.td-sp>62</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>英語</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
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


{{-- 定期考査 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true  vShow="item.id == 1">
    <tr>
        <th width="35%">登録日</th>
        <td>2023/05/15</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>種別</th>
        <td>定期考査</td>
    </tr>
    <tr>
        <th>試験名</th>
        <td>１学期中間考査</td>
    </tr>

    <tr>
        <th colspan="2">成績</th>
    </tr>
    <tr>
        <td colspan="2">
            {{-- tableの中にtableを書くと線が出てしまう noborder-only-topを指定した --}}
            <x-bs.table :bordered=false :hover=false class="noborder-only-top">
                <x-slot name="thead">
                    <th width="30%">教科</th>
                    <th width="30%">得点</th>
                    <th width="30%">学年平均</th>
                </x-slot>

                <tr>
                    <x-bs.td-sp>全教科合計</x-bs.td-sp>
                    <x-bs.td-sp>380点</x-bs.td-sp>
                    <x-bs.td-sp>250点</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>国語</x-bs.td-sp>
                    <x-bs.td-sp>80点</x-bs.td-sp>
                    <x-bs.td-sp>50点</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>数学</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp>50点</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>理科</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp>50点</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>社会</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp>50点</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>英語</x-bs.td-sp>
                    <x-bs.td-sp>75点</x-bs.td-sp>
                    <x-bs.td-sp>50点</x-bs.td-sp>
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


{{-- 評定 --}}
<x-bs.table :hover=false :vHeader=true :smartPhoneModal=true vShow="item.id == 3">
    <tr>
        <th width="35%">登録日</th>
        <td>2023/07/21</td>
    </tr>
    <tr>
        <th>生徒名</th>
        <td>CWテスト生徒１</td>
    </tr>
    <tr>
        <th>種別</th>
        <td>通信票評定</td>
    </tr>
    <tr>
        <th>学期</th>
        <td>2学期</td>
    </tr>

    <tr>
        <th colspan="2">成績</th>
    </tr>
    <tr>
        <td colspan="2">
            {{-- tableの中にtableを書くと線が出てしまう noborder-only-topを指定した --}}
            <x-bs.table :bordered=false :hover=false class="noborder-only-top">
                <x-slot name="thead">
                    <th width="50%">教科</th>
                    <th width="50%">評定値</th>
                </x-slot>

                <tr>
                    <x-bs.td-sp>国語</x-bs.td-sp>
                    <x-bs.td-sp>5</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>数学</x-bs.td-sp>
                    <x-bs.td-sp>5</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>理科</x-bs.td-sp>
                    <x-bs.td-sp>5</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>社会</x-bs.td-sp>
                    <x-bs.td-sp>5</x-bs.td-sp>
                </tr>
                <tr>
                    <x-bs.td-sp>英語</x-bs.td-sp>
                    <x-bs.td-sp>5</x-bs.td-sp>
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