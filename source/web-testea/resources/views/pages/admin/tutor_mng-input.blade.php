@extends('adminlte::page')

@section('title', (request()->routeIs('tutor_mng-edit')) ? '教師編集' : '教師登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('tutor_mng-edit'))
    {{-- 編集時 --}}
    <p>以下の教師について、編集を行います。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="15%">教師No</th>
            <td>101</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>
    <x-input.text caption="教師名" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="メールアドレス" id="email" :rules=$rules :editData=$editData/>
    <x-input.text caption="電話番号" id="tel" :rules=$rules :editData=$editData/>
    <x-input.text caption="基本給" id="basepay" :rules=$rules :editData=$editData/>
    <x-input.text caption="交通費" id="transportation_cost" :rules=$rules :editData=$editData/>
    <x-input.select caption="表示フラグ" id="display_flag" :select2=true :blank=false :editData=$editData>
        <option value="1">表示</option>
        <option value="2">非表示</option>
    </x-input.select>

    @else
    {{-- 登録時 --}}
    <p>教師の登録を行います。</p>
    <x-input.text caption="教師名" id="name" :rules=$rules />
    <x-input.text caption="メールアドレス" id="email" :rules=$rules />
    <x-input.text caption="電話番号" id="tel" :rules=$rules />
    <x-input.text caption="基本給" id="basepay" :rules=$rules />
    <x-input.text caption="交通費" id="transportation_cost" :rules=$rules />
    <x-input.select caption="表示フラグ" id="display_flag" :select2=true :blank=false :editData=$editData>
        <option value="1">表示</option>
        <option value="2">非表示</option>
    </x-input.select>

    @endif

    {{-- hidden --}}
    <x-input.hidden id="tid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('tutor_mng-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                {{-- 削除機能なし --}}
                {{-- <x-button.submit-delete /> --}}
                <x-button.submit-edit />
            </div>
            @else
            {{-- 登録時 --}}
            <x-button.submit-new />
            @endif

        </div>
    </x-slot>

</x-bs.card>

@stop