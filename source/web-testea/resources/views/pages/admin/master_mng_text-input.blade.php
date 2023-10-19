@extends('adminlte::page')

@section('title', (request()->routeIs('master_mng_text-edit')) ? '授業教材マスタデータ編集' : '授業教材マスタデータ登録')

{{-- 子ページ --}}
@section('child_page', true)

@section('content')

{{-- formを指定 --}}
<x-bs.card :form=true>

    @if (request()->routeIs('master_mng_text-edit'))
    {{-- 編集時 --}}
    <p>以下の授業教材情報について編集を行います。</p>
    {{-- 余白 --}}
    <div class="mb-3"></div>

    @else
    {{-- 登録時 --}}
    <p>
        授業教材の登録を行います。<br>
    </p>
    @endif

    {{-- 共通フォーム --}}
    <x-input.text caption="教材コード　(学年コード(2桁) + 授業科目コード(3桁) + 科目連番(1桁) + 教材名連番(2桁・その他は99))" id="text_cd" :rules=$rules :editData=$editData/>
    <x-input.select caption="学年" id="grade_cd" :select2=true :editData=$editData>
        <option value="7">07（中1）</option>
        <option value="8">08（中2）</option>
        <option value="9">09（中3）</option>
    </x-input.select>
    <x-input.select caption="授業科目コード" id="l_subject_cd" :select2=true :editData=$editData>
        <option value="101">101（英語）</option>
        <option value="102">102（数学）</option>
        <option value="103">103（国語）</option>
        <option value="503">503（数学・英語）</option>
    </x-input.select>
    <x-input.select caption="教材科目コード" id="t_subject_cd" :select2=true :editData=$editData>
        <option value="101">101（英語）</option>
        <option value="102">102（数学）</option>
        <option value="103">103（国語）</option>
        <option value="503">503（数学・英語）</option>
    </x-input.select>
    
    {{-- <x-input.select caption="科目連番" id="t_subject_no" :select2=true :editData=$editData>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
        <option value="7">7</option>
        <option value="8">8</option>
        <option value="9">9</option>
    </x-input.select>
    
    <x-input.select caption="教材名連番" id="name_no" :select2=true :editData=$editData>
        <option value="01">01</option>
        <option value="02">02</option>
        <option value="03">03</option>
        <option value="04">04</option>
        <option value="05">05</option>
        <option value="06">06</option>
        <option value="07">07</option>
        <option value="08">08</option>
        <option value="09">09</option>
        <option value="10">10</option>
        <option value="99">99(その他)</option>
    </x-input.select> --}}

    <x-input.text caption="名称" id="name" :rules=$rules :editData=$editData/>

    {{-- フッター --}}
    <x-slot name="footer">
        <div class="d-flex justify-content-between">
            <x-button.back />

            @if (request()->routeIs('master_mng_text-edit'))
            {{-- 編集時 --}}
            <div class="d-flex justify-content-end">
                <x-button.submit-delete />
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