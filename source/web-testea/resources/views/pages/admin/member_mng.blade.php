@extends('adminlte::page')

@section('title', '会員一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            {{-- @can('roomAdmin') --}}
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            {{-- <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
                :select2Search=false :blank=false />
            @else
            <x-input.select id="roomcd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData />
            @endcan --}}
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
            {{-- <x-input.select id="cls_cd" caption="学年" :select2=true :editData=$editData :mastrData=$classes /> --}}
            <x-input.select id="cls_cd" caption="学年" :select2=true >
                <option value="1">高3</option>
                <option value="2">高2</option>
                <option value="3">高1</option>
                <option value="4">中3</option>
                <option value="5">中2</option>
                <option value="6">中1</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.text id="sid" caption="生徒No" :rules=$rules />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.text id="name" caption="生徒名" :rules=$rules />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-bs.form-group name="status_groups">
                <x-bs.form-title>対象生徒</x-bs.form-title>
                {{-- 教科チェックボックス --}}
                @for ($i = 0; $i < count($statusGroup); $i++)
                <x-input.checkbox :caption="$statusGroup[$i]"
                        :id="'status_group_' . $statusGroup[$i]"
                        name="status_groups" :value="$statusGroup[$i]" />
                @endfor
            </x-bs.form-group>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="period" caption="通塾期間" :select2=true >
                <option value="1">0年～1年</option>
                <option value="2">1年～2年</option>
                <option value="3">2年～3年</option>
                <option value="4">3年～4年</option>
            </x-input.select>
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.new href="{{ route('member_mng-new') }}" :small=true />
        <x-button.submit-exec caption="CSVダウンロード" icon="fas fa-download" />
    </x-slot>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th>生徒No</th>
            <th>生徒名</th>
            <th>メールアドレス</th>
            <th>学年</th>
            <th>入会日</th>
            <th>通塾期間</th>
            <th>通塾バッジ数</th>
            <th>受講科目</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr>
            <td>1</td>
            <td>CWテスト生徒１</td>
            <td>student0001@ap.jeez.jp</td>
            <td>中学１年</td>
            <td>2023/4/1</td>
            <td>2ヶ月</td>
            <td>0</td>
            <td>数学</td>
            <td>
                <x-button.list-dtl href="{{ route('member_mng-detail', 1) }}" caption="生徒カルテ" />
            </td>
        </tr>
        <tr>
            <td>2</td>
            <td>CWテスト生徒２</td>
            <td>student0002@ap.jeez.jp</td>
            <td>中学１年</td>
            <td>2021/12/1</td>
            <td>1年6ヶ月</td>
            <td>1</td>
            <td>英語</td>
            <td>
                <x-button.list-dtl href="{{ route('member_mng-detail', 2) }}" caption="生徒カルテ" />
            </td>
        </tr>
        <tr>
            <td>3</td>
            <td>CWテスト生徒３</td>
            <td>student0003@ap.jeez.jp</td>
            <td>中学２年</td>
            <td>2022/6/1</td>
            <td>1年0ヶ月</td>
            <td>0</td>
            <td>英語</td>
            <td>
                <x-button.list-dtl href="{{ route('member_mng-detail', 3) }}" caption="生徒カルテ" />
            </td>
        </tr>
        <tr>
            <td>4</td>
            <td>CWテスト生徒４</td>
            <td>student0004@ap.jeez.jp</td>
            <td>中学２年</td>
            <td>2022/4/1</td>
            <td>1年2ヶ月</td>
            <td>1</td>
            <td>数学</td>
            <td>
                <x-button.list-dtl href="{{ route('member_mng-detail', 4) }}" caption="生徒カルテ" />
            </td>
        </tr>
        <tr>
            <td>5</td>
            <td>CWテスト生徒５</td>
            <td>student0005@ap.jeez.jp</td>
            <td>中学３年</td>
            <td>2019/7/1</td>
            <td>3年11ヶ月</td>
            <td>3</td>
            <td>国語・数学・英語</td>
            <td>
                <x-button.list-dtl href="{{ route('member_mng-detail', 5) }}" caption="生徒カルテ" />
            </td>
        </tr>
        <tr>
            <td>6</td>
            <td>CWテスト生徒６</td>
            <td>student0006@ap.jeez.jp</td>
            <td>中学３年</td>
            <td>2021/4/1</td>
            <td>2年2ヶ月</td>
            <td>2</td>
            <td>国語</td>
            <td>
                <x-button.list-dtl href="{{ route('member_mng-detail', 6) }}" caption="生徒カルテ" />
            </td>
        </tr>

        {{-- 本番用処理 --}}
        {{-- <tr v-for="item in paginator.data" v-cloak>
            <td>@{{item.sid}}</td>
            <td>@{{item.name}}</td>
            <td>@{{item.mailaddress1}}</td>
            <td>@{{item.cls_name}}</td>
            <td>@{{item.enter_date|formatYmd}}</td>
            <td>3年5ヶ月</td>
            <td>1</td>
            <td>数学</td>
            <td>
                <x-button.list-dtl vueHref="'{{ route('member_mng-detail', '') }}/' + item.sid" caption="生徒カルテ" />
            </td>
        </tr> --}}

    </x-bs.table>

</x-bs.card-list>

@stop