@extends('adminlte::page')

@section('title', '特別期間講習 生徒日程一覧')

@section('content')

{{-- 検索フォーム --}}
<x-bs.card :search=true>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="season_cd" caption="特別期間" :select2=true :mastrData=$seasonList :editData=$editData
                :rules=$rules :select2Search=false :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            @can('roomAdmin')
            {{-- 教室管理者の場合、1つなので検索や未選択を非表示にする --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
				:select2Search=false :blank=false />
            @else
            {{-- 全体管理者の場合、検索を非表示・未選択を表示する --}}
            <x-input.select id="campus_cd" caption="校舎" :select2=true :mastrData=$rooms :editData=$editData
				:select2Search=false :blank=true />
            @endcan
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="student_id" caption="生徒名" :select2=true :mastrData=$students :editData=$editData
                :rules=$rules :select2Search=true :blank=true />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="regist_status" caption="生徒登録状態" :select2=true :mastrData=$regStatusList :editData=$editData
                :rules=$rules :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>
    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="plan_status" caption="コマ組み状態" :select2=true :mastrData=$planStatusList :editData=$editData
                :rules=$rules :select2Search=false :blank=true />
        </x-bs.col2>
    </x-bs.row>

</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- テーブル --}}
    <x-bs.table :button=true>

        {{-- テーブルタイトル行 --}}
        <x-slot name="thead">
            <th width="15%">連絡日</th>
            <th>特別期間名</th>
            <th>校舎</th>
            <th>生徒名</th>
            <th>生徒登録状態</th>
            <th>コマ組み状態</th>
            <th></th>
        </x-slot>

        {{-- テーブル行 --}}
        <tr v-for="item in paginator.data" v-cloak>
            <td>@{{$filters.formatYmd(item.apply_date)}}</td>
            <td>@{{item.year}}年@{{item.season_name}}</td>
            <td>@{{item.campus_name}}</td>
            <td>@{{item.student_name}}</td>
            <td>@{{item.regstatus_name}}</td>
            <td>@{{item.planstatus_name}}</td>
            <td>
                <x-button.list-edit vueHref="'{{ route('season_mng_student-detail', '') }}/' + item.season_student_id"
                caption="詳細・コマ組み" vueDisabled="item.regist_status == {{ App\Consts\AppConst::CODE_MASTER_5_0 }}" />
            </td>
        </tr>
    </x-bs.table>
</x-bs.card-list>

@stop
