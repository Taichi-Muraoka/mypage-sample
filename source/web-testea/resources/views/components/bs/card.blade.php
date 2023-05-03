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
--}}
@props(['card_title' => '', 'tools' => '', 'footer' => '', 'id' => '', 
    'search' => false, 'form' => false, 'p0' => false, 'class' => '', 'vShow' => ''])

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

        {{-- 検索フォームの場合 --}}
        @if ($search)
        <div class="d-flex justify-content-end">
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