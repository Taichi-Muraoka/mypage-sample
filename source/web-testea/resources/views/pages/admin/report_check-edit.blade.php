@extends('adminlte::page')

@section('title', '授業報告書編集')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    <p>以下の授業報告書の変更を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th>登録日</th>
            <td>2023/05/15</td>
        </tr>
        <tr>
            <th>授業種別</th>
            <td>個別指導</td>
        </tr>
        <tr>
            <th width="35%">授業日・時限</th>
            {{-- <td>{{$editData->lesson_date->format('Y/m/d')}} {{$editData->start_time->format('H:i')}}</td> --}}
            <td>2023/05/15 4限</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>久我山</td>
        </tr>
        <tr>
            <th>生徒名</th>
            <td>CWテスト生徒１</td>
        </tr>
        <tr>
            <th>科目</th>
            <td>数学</td>
        </tr>
        <tr>
            <th>今月の目標</th>
            <td>正負の数の計算をマスターする</td>
        </tr>
        <tr>
            <th>授業教材１</th>
            <td>中１数学ドリル演習 p13-18</td>
        </tr>
        <tr>
            <th>授業単元１</th>
            <td>正負の数・乗法と除法<br>
                正負の数・四則の混じった計算<br>
                正負の数・その他（単元まとめ）
            </td>
        </tr>
        <tr>
            <th>授業教材２</th>
            <td></td>
        </tr>
        <tr>
            <th>授業単元２</th>
            <td></td>
        </tr>
        <tr>
            <th>確認テスト内容</th>
            <td>数学ドリル p19</td>
        </tr>
        <tr>
            <th>確認テスト得点</th>
            <td>10/10点</td>
        </tr>
        <tr>
            <th>宿題達成度</th>
            <td>100%</td>
        </tr>
        <tr>
            <th>達成・課題点</th>
            {{-- nl2br: 改行 --}}
            <td class="nl2br">よく理解できています</td>
        </tr>
        <tr>
            <th>解決策</th>
            <td class="nl2br"></td>
        </tr>
        <tr>
            <th>その他</th>
            <td class="nl2br"></td>
        </tr>
        <tr>
            <th>宿題教材１</th>
            <td>中１数学ドリル演習 p19-20</td>
        </tr>
        <tr>
            <th>宿題単元１</th>
            <td>
                正負の数・その他（単元まとめ）
            </td>
        </tr>
        <tr>
            <th>宿題教材２</th>
            <td></td>
        </tr>
        <tr>
            <th>宿題単元２</th>
            <td></td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.select caption="承認ステータス" id="status" :select2=true :editData=$editData>
        <option value="1">承認待ち</option>
        <option value="2">承認</option>
        <option value="3">差戻し</option>
    </x-input.select>

    <x-input.textarea caption="管理者コメント" id="admin_comment" :rules=$rules :editData=$editData />

    {{-- hidden --}}
    <x-input.hidden id="report_id" :editData=$editData />
    {{-- hidden --}}
    <x-input.hidden id="tid" :editData=$editData />

    <x-bs.callout title="登録の際の注意事項" type="warning">
        承認ステータスを「承認」として更新ボタンを押下すると、 生徒に授業報告書が開示されます。
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
                <x-button.submit-edit />
            </div>
        </div>
    </x-slot>

</x-bs.card>

@stop