@extends('adminlte::page')

@section('title', '特別期間講習 受付期間登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :form=true>

    {{-- hidden --}}
    <x-input.hidden id="season_mng_id" :editData=$editData />

    <p>以下の特別期間講習について、受付期間の登録を行います。</p>

    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="35%">特別期間</th>
            <td>2023年夏期</td>
        </tr>
        <tr>
            <th>校舎</th>
            <td>久我山</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-input.date-picker caption="講師受付開始日" id="t_start_date" :editData=$editData />

    <x-input.date-picker caption="講師受付終了日" id="t_end_date" :editData=$editData />

    <x-input.date-picker caption="生徒受付開始日" id="s_start_date" :editData=$editData />

    <x-input.date-picker caption="生徒受付終了日" id="s_end_date" :editData=$editData />

    {{-- 余白 --}}
    <div class="mb-3"></div>

    <x-bs.callout type="warning">
        講師受付終了日は、講習開始日以前の日付を設定してください。<br>
        生徒受付終了日は、講習終了日以前の日付を設定してください。<br>
    </x-bs.callout>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                {{-- <x-button.submit-delete /> --}}
                <x-button.submit-edit caption="登録"/>
            </div>

        </div>
    </x-slot>

</x-bs.card>

@stop