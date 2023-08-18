@extends('adminlte::page')

@section('title', '成績一覧')

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
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.select id="destination_type" caption="学年" :select2=true>
                <option value="1">小学校</option>
                <option value='2'>中学校</option>
                <option value='3'>高等学校</option>
                <option value='4'>その他</option>
            </x-input.select>

            <x-bs.card  v-show="form.destination_type == 1">
                <x-bs.form-group name="notice_groups_p">
                    {{-- 学年チェックボックス --}}
                    @for ($i = 0; $i < count($noticeGroup_p); $i++)
                    <x-input.checkbox :caption="$noticeGroup_p[$i]"
                            :id="'notice_group_p_' . $noticeGroup_p[$i]"
                            name="notice_groups_p" :value="$noticeGroup_p[$i]" />
                    @endfor
                </x-bs.form-group>
            </x-bs.card>

            <x-bs.card  v-show="form.destination_type == 2">
                <x-bs.form-group name="notice_groups_j">
                    {{-- 学年チェックボックス --}}
                    @for ($i = 0; $i < count($noticeGroup_j); $i++)
                    <x-input.checkbox :caption="$noticeGroup_j[$i]"
                            :id="'notice_group_j_' . $noticeGroup_j[$i]"
                            name="notice_groups_j" :value="$noticeGroup_j[$i]" />
                    @endfor
                </x-bs.form-group>
            </x-bs.card>

            <x-bs.card  v-show="form.destination_type == 3">
                <x-bs.form-group name="notice_groups_h">
                    {{-- 学年チェックボックス --}}
                    @for ($i = 0; $i < count($noticeGroup_h); $i++)
                    <x-input.checkbox :caption="$noticeGroup_h[$i]"
                            :id="'notice_group_h_' . $noticeGroup_h[$i]"
                            name="notice_groups_h" :value="$noticeGroup_h[$i]" />
                    @endfor
                </x-bs.form-group>
            </x-bs.card>
        </x-bs.col2>
        <x-bs.col2>
            <x-input.select id="exam_kinds" caption="種別" :select2=true>
                <option value="1">模試</option>
                <option value="2">定期考査</option>
                <option value="3">通信票評定</option>
            </x-input.select>

            <x-bs.card  v-show="form.exam_kinds == 2">
                <x-bs.form-group name="regular_exam">
                    {{-- 定期考査コードチェックボックス --}}
                    @for ($i = 0; $i < count($regular_exam); $i++)
                    <x-input.checkbox :caption="$regular_exam[$i]"
                            :id="'regular_exam_' . $regular_exam[$i]"
                            name="regular_exam" :value="$regular_exam[$i]" />
                    @endfor
                </x-bs.form-group>
            </x-bs.card>

            <x-bs.card  v-show="form.exam_kinds == 3">
                <x-bs.form-group name="term">
                    {{-- 学期コードチェックボックス --}}
                    @for ($i = 0; $i < count($term); $i++)
                    <x-input.checkbox :caption="$term[$i]"
                            :id="'term_' . $term[$i]"
                            name="term" :value="$term[$i]" />
                    @endfor
                </x-bs.form-group>
            </x-bs.card>
        </x-bs.col2>
    </x-bs.row>

    <x-bs.row>
        <x-bs.col2>
            <x-input.date-picker caption="対象期間 From" id="date_from" />
        </x-bs.col2>
        <x-bs.col2>
            <x-input.date-picker caption="対象期間 To" id="date_to" />
        </x-bs.col2>
    </x-bs.row>
</x-bs.card>

{{-- 結果リスト --}}
<x-bs.card-list>

    {{-- カードヘッダ右 --}}
    <x-slot name="tools">
        <x-button.submit-exec caption="CSVダウンロード" icon="fas fa-download" />
    </x-slot>

    {{-- テーブル --}}
    <div class="table-responsive">
        <x-bs.table :button=true class="table text-nowrap">

            {{-- テーブルタイトル行 --}}
            <x-slot name="thead">
                <th width="15%">登録日</th>
                <th width="15%">校舎</th>
                <th width="15%">学年</th>
                <th width="20%">生徒名</th>
                <th width="15%">種別</th>
                <th width="20%">学期・試験名</th>
            </x-slot>

            {{-- テーブル行 --}}
            <tr>
                <td>2023/07/21</td>
                <td>久我山</td>
                <td>中学2年</td>
                <td>CWテスト生徒１</td>
                <td>通信票評定</td>
                <td>1学期（前期）</td>
            </tr>
            <tr>
                <td>2023/04/10</td>
                <td>久我山</td>
                <td>中学2年</td>
                <td>CWテスト生徒１</td>
                <td>定期考査</td>
                <td>1学期（前期）中間考査</td>
            </tr>
            <tr>
                <td>2023/03/18</td>
                <td>久我山</td>
                <td>中学2年</td>
                <td>CWテスト生徒１</td>
                <td>模試</td>
                <td>全国統一模試</td>
            </tr>

        </x-bs.table>
    </div>

</x-bs.card-list>

@stop