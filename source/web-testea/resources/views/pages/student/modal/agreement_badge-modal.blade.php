@extends('pages.common.modal')

@section('modal-body')

{{------------------------}}
{{-- モック用モーダル画面--}}
{{------------------------}}
<x-bs.form-title>バッジ付与履歴</x-bs.form-title>

{{-- 最大10件なのでページネータなし --}}
<x-bs.table :smartPhoneModal=true class="modal-fix">

    <x-slot name="thead">
        <th>付与日</th>
        <th>校舎</th>
        <th>認定理由</th>
    </x-slot>
    <tr>
        <x-bs.td-sp caption="付与日">2023/05/10</x-bs.td-sp>
        <x-bs.td-sp caption="校舎">久我山</x-bs.td-sp>
        <x-bs.td-sp caption="認定理由">生徒紹介（佐藤次郎さん）</x-bs.td-sp>
    </tr>
    <tr>
        <x-bs.td-sp caption="付与日">2022/03/20</x-bs.td-sp>
        <x-bs.td-sp caption="校舎">久我山</x-bs.td-sp>
        <x-bs.td-sp caption="認定理由">生徒紹介（仙台太郎さん）</x-bs.td-sp>
    </tr>

</x-bs.table>

@overwrite
