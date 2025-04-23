@extends('pages.common.modal')

@section('modal-body')

<x-bs.form-title>バッジ付与履歴</x-bs.form-title>

<x-bs.table :smartPhoneModal=true class="modal-fix">

    <x-slot name="thead">
        <th width="25%">付与日</th>
        <th width="25%">校舎</th>
        <th width="50%">認定理由</th>
    </x-slot>

    <tr v-for="badge in item.badgeList" v-cloak>
        <x-bs.td-sp caption="付与日">@{{$filters.formatYmd(badge.authorization_date)}}</x-bs.td-sp>
        <x-bs.td-sp caption="校舎">@{{badge.campus_name}}</x-bs.td-sp>
        <x-bs.td-sp caption="認定理由">@{{badge.reason}}</x-bs.td-sp>
    </tr>

</x-bs.table>

@overwrite
