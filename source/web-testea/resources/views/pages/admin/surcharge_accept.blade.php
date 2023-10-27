@extends('adminlte::page')

@section('title', '追加請求申請受付一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="roomcd" caption="校舎" :select2=true >
                <option value="1">久我山</option>
                <option value="2">西永福</option>
                <option value="3">下高井戸</option>
                <option value="4">駒込</option>
                <option value="5">日吉</option>
                <option value="6">自由が丘</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select caption="講師名" id="tname" :select2=true :editData=$editData>
                <option value="1">CWテスト教師１０１</option>
                <option value="2">CWテスト教師１０２</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="kinds" caption="請求種別" :select2=true>
                <option value="1">研修（本部）</option>
                <option value="2">研修（教室）</option>
                <option value="3">特別交通費</option>
                <option value="4">生徒獲得</option>
                <option value="5">業務依頼（本部）</option>
                <option value="6">業務依頼（教室）</option>
                <option value="7">経費</option>
                <option value="8">その他</option>
            </x-input.select>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="status" caption="ステータス" :select2=true>
                <option value="1">承認待ち</option>
                <option value="2">承認</option>
                <option value="3">差戻し</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="payment" caption="支払状況" :select2=true>
                <option value="1">未処理</option>
                <option value="2">支払済</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="申請日 From" id="holiday_date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="申請日 To" id="holiday_date_to" />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true :smartPhone=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>申請日</th>
            <th>講師名</th>
            <th>請求種別</th>
            <th>校舎</th>
            <th>時間（分）</th>
            <th>金額</th>
            <th>ステータス</th>
            <th>支払年月</th>
            <th>支払状況</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>2023/01/10</td>
            <td>CWテスト教師１０１</td>
            <td>業務依頼（教室）</td>
            <td>久我山</td>
            <td>60</td>
            <td>1000</td>
            <td>承認</td>
            <td>2023/02</td>
            <td>未処理</td>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '1']"/>
                <x-button.list-dtl caption="承認" btn="btn-primary" dataTarget="#modal-dtl-acceptance" :vueDataAttr="['id' => '1']" disabled/>
                <x-button.list-edit href="{{ route('surcharge_accept-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>2023/01/09</td>
            <td>CWテスト教師１０１</td>
            <td>経費</td>
            <td>久我山</td>
            <td></td>
            <td>2000</td>
            <td>承認待ち</td>
            <td></td>
            <td></td>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '2']"/>
                <x-button.list-dtl caption="承認" btn="btn-primary" dataTarget="#modal-dtl-acceptance" :vueDataAttr="['id' => '2']" />
                <x-button.list-edit href="{{ route('surcharge_accept-edit', 1) }}" />
            </td>
        </tr>
        <tr>
            <td>2023/01/5</td>
            <td>CWテスト教師１０３</td>
            <td>業務依頼（教室）</td>
            <td>西永福</td>
            <td>90</td>
            <td>1500</td>
            <td>承認待ち</td>
            <td></td>
            <td></td>
            <td>
                <x-button.list-dtl :vueDataAttr="['id' => '3']"/>
                <x-button.list-dtl caption="承認" btn="btn-primary" dataTarget="#modal-dtl-acceptance" :vueDataAttr="['id' => '3']" />
                <x-button.list-edit href="{{ route('surcharge_accept-edit', 1) }}" />
            </td>
        </tr>
    </x-bs.table>

</x-bs.card-list>

{{-- モーダル --}}
@include('pages.admin.modal.surcharge_accept-modal')
{{-- モーダル(送信確認モーダル) --}}
@include('pages.admin.modal.surcharge_accept_acceptance-modal', ['modal_send_confirm' => true, 'modal_id' =>
'modal-dtl-acceptance'])

@stop