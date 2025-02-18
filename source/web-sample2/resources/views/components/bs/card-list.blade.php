{{------------------------------------------ 
    card list 結果一覧など(ページャ付き)
  --------------------------------------------}}

{{-- 
  mock: モック時の表示
--}}
@props(['id' => '', 'tools' => '', 'mock' => false])

{{-- ページャ番号押下時のスクロール先 --}}
<span id="search-top"></span>
{{-- リスト --}}
<div class="card" id="@if (!empty($id)){{ $id }}@else{{'app-serch-list'}}@endif">
    <div class="card-header">

        {{-- トータル件数を表示する --}}
        @can('admin')
        @if ($mock)
        {{-- モック --}}
        <span class="text-muted">20件</span>
        @else
        <span class="text-muted" v-cloak>@{{paginator.total}}</span>
        @endif
        @endcan

        {{-- 新規登録ボタン --}}
        @if (!empty($tools))
        <div class="card-tools">
            {{ $tools }}
        </div>
        @endif
    </div>

    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
        {{ $slot }}
    </div>

    <div class="card-footer clearfix" @if (!$mock) v-cloak @endif>

        @if ($mock)
        {{-- モック --}}
        <ul class="pagination pagination-sm m-0 float-right">
            <li class="page-item">
                <a href="#" class="page-link">&laquo;</a>
            </li>
            <li class="page-item active">
                <span class="page-link" v-on:click.prevent="page_link(1)">1</span>
            </li>
            <li class="page-item">
                <a href="#" class="page-link" v-on:click.prevent="page_link(1)">2</a>
            </li>
            <li class="page-item">
                <a href="#" class="page-link" v-on:click.prevent="page_link(1)">3</a>
            </li>
            <li class="page-item">
                <a href="#" class="page-link" v-on:click.prevent="page_link(1)">&raquo;</a>
            </li>
        </ul>
        @else
        {{-- ページャ Vue対応 --}}
        <ul class="pagination pagination-sm m-0 float-right">

            {{-- 前へ --}}
            <li class="page-item" v-bind:class="{ disabled: paginator.current_page <= 1 }" aria-disabled="true">
                <span class="page-link" aria-hidden="true" v-if="paginator.current_page <= 1">&laquo;</span>
                <a class="page-link" href="#" v-if="paginator.current_page > 1"
                    v-on:click.prevent="page_link(paginator.current_page-1)">&laquo;</a>
            </li>

            {{-- 最初 --}}
            <li class="page-item" v-for="(element, index) in elements[0]"
                v-bind:class="{ active: index == paginator.current_page }" v-cloak>
                <a href="#" class="page-link" v-if="index != paginator.current_page" v-text="index"
                    v-on:click.prevent="page_link(index)"></a>
                <span class="page-link" v-if="index == paginator.current_page" v-text="index"></span>
            </li>

            {{-- ... --}}
            <li class="page-item disabled" v-if="elements[1]" aria-disabled="true" v-cloak><span class="page-link"
                    v-text="elements[1]"></span>
            </li>

            {{-- 真ん中 --}}
            <li class="page-item" v-for="(element, index) in elements[2]"
                v-bind:class="{ active: index == paginator.current_page }" v-cloak>
                <a href="#" class="page-link" v-if="index != paginator.current_page" v-text="index"
                    v-on:click.prevent="page_link(index)"></a>
                <span class="page-link" v-if="index == paginator.current_page" v-text="index"></span>
            </li>

            {{-- ... --}}
            <li class="page-item disabled" v-if="elements[3]" aria-disabled="true" v-cloak><span class="page-link"
                    v-text="elements[3]"></span>
            </li>

            {{-- 最後 --}}
            <li class="page-item" v-for="(element, index) in elements[4]"
                v-bind:class="{ active: index == paginator.current_page }" v-cloak>
                <a href="#" class="page-link" v-if="index != paginator.current_page" v-text="index"
                    v-on:click.prevent="page_link(index)"></a>
                <span class="page-link" v-if="index == paginator.current_page" v-text="index"></span>
            </li>

            {{-- 次へ --}}
            <li class="page-item" v-bind:class="{ disabled: paginator.current_page >= paginator.last_page }"
                aria-disabled="true">
                <span class="page-link" aria-hidden="true"
                    v-if="paginator.current_page >= paginator.last_page">&raquo;</span>
                <a class="page-link" href="#" v-if="paginator.current_page < paginator.last_page"
                    v-on:click.prevent="page_link(paginator.current_page+1)">&raquo;</a>
            </li>
        </ul>
        @endif

    </div>
</div>
<!-- /.card -->