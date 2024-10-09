{{------------------------------------------ 
    card
  --------------------------------------------}}

{{--
    card_title: タイトル
    tools: ヘッダのボタンの表示など
    footer: フッター
    id: divのID
    search: 検索フォームの場合
    form: フォームの場合
    p0: card-bodyのスペースを0にする
    class: クラス
    vShow: vueのv-showの指定
    initSearchCond: 検索フォームかつ検索条件引継ぎを行う場合true
    initSearchParam: 検索フォームかつページ番号の引き継ぎを行う場合にパラメータを指定
--}}
@props(['card_title' => '', 'tools' => '', 'footer' => '', 'id' => '', 
    'search' => false, 'form' => false, 'p0' => false, 'class' => '', 'vShow' => '',
    'initSearchCond' => false, 'initSearchParam' => null])

<div class="card @if ($form) card-form @endif @if (!empty($class)){{ $class }}@endif"
    {{-- id --}} 
    @if ($search) id="app-serch-form" @elseif ($form) id="app-form" @elseif (!empty($id)) id="{{ $id }}" @endif

    {{-- v-show --}}
    @if ($vShow)
    v-show="{{ $vShow }}"
    @endif
    >

    @if (!empty($card_title) || $tools)
    <div class="card-header">

        {{-- タイトル --}}
        @if (!empty($card_title))
        <h6 class="card-title">{{ $card_title }}</h6>
        @endif

        {{-- 新規登録ボタンなど --}}
        @if (!empty($tools))
        <div class="card-tools">
            {{ $tools }}
        </div>
        @endif

    </div>
    @endif

    <!-- /.card-header -->
    <div class="card-body @if ($p0) {{'p-0'}} @endif">
        {{ $slot }}

        {{-- 初期検索条件（ページ番号） --}}
        @if ($initSearchParam)
        <x-input.hidden id="init_search_param_page" :editData=$initSearchParam />
        @endif

        {{-- 検索フォームの場合 --}}
        @if ($search)
        <div class="d-flex justify-content-end">

            {{-- 検索条件引継ぎを行う場合 クリアボタン表示 --}}
            @if ($initSearchCond)
            <button type="button" class="btn btn-secondary mr-3" v-on:click="initSearchCondClear" v-bind:disabled="disabledBtnSearch">
                <i class="fas fa-trash"></i>
                クリア
            </button>
            @endif

            <x-button.search />
        </div>
        @endif
    </div>

    @if (!empty($footer))
    <div class="card-footer clearfix">

        {{-- フッター --}}
        @if (!empty($footer))
        {{ $footer }}
        @endif

    </div>
    @endif
</div>
<!-- /.card -->