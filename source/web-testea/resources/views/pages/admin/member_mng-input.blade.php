@extends('adminlte::page')

@section('title', (request()->routeIs('member_mng-edit')) ? '会員編集' : '会員登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('member_mng-edit'))
    {{-- 編集時 --}}
    <p>以下の会員について、編集を行います。</p>
    <x-bs.table :hover=false :vHeader=true>
        <tr>
            <th width="15%">会員No</th>
            <td>1</td>
        </tr>
    </x-bs.table>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>会員の登録を行います。</p>

    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="生徒名" id="name" :rules=$rules :editData=$editData/>
    <x-input.text caption="生徒名カナ" id="name_kana" :rules=$rules :editData=$editData/>
    <x-input.select caption="学年" id="cls_cd" :select2=true :blank=false :editData=$editData :mastrData=$classes/>
    <x-input.text caption="電話番号（生徒）" id="tel_student" :rules=$rules :editData=$editData/>
    <x-input.text caption="電話番号（保護者）" id="tel_guardian" :rules=$rules :editData=$editData/>
    <x-input.text caption="Email（生徒）" id="email_student" :rules=$rules :editData=$editData/>
    <x-input.text caption="Email（保護者）" id="email_guardian" :rules=$rules :editData=$editData/>
    <x-input.select caption="受講コース" id="courses" :select2=true :blank=false :editData=$editData>
        <option value="1">個別指導コース</option>
        <option value="2">集団授業</option>
    </x-input.select>
    <x-input.select caption="所属校舎" id="schools" :select2=true :blank=false :editData=$editData>
        <option value="1">久我山</option>
        <option value="2">西永福</option>
        <option value="2">下高井戸</option>
        <option value="2">駒込</option>
        <option value="2">日吉</option>
        <option value="2">自由が丘</option>
    </x-input.select>
    <x-input.select caption="受験生フラグ" id="jukensei" :select2=true :blank=false :editData=$editData>
        <option value="1">非受験生</option>
        <option value="2">受験生</option>
    </x-input.select>
    <x-input.select caption="表示フラグ" id="display_flag" :select2=true :blank=false :editData=$editData>
        <option value="1">表示</option>
        <option value="2">非表示</option>
    </x-input.select>

    {{-- hidden --}}
    <x-input.hidden id="sid" :editData=$editData />

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('member_mng-edit'))
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