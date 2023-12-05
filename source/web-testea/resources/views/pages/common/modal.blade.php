{{------------------------------------------ 
    modal
  --------------------------------------------}}

{{--
    modal_id: モーダルのID
    modal_send_confirm: 送信確認用のダイアログ。fadeは無効にし、タイトル行は非表示にする
    modal-size: モーダルのサイズ
    modal_form: モーダルでフォームを表示
    caption_OK: OKボタン表示名
--}}

{{-- モーダルを複数使用する場合はmodalIdを指定する --}}
<div 
    {{-- class --}}
    class="modal @if (!isset($modal_send_confirm) || (!$modal_send_confirm)) {{'fade'}} @endif" 
    {{-- id --}}
    id="@if (empty($modal_id)){{ 'modal-dtl' }}@else{{ $modal_id }}@endif"
    ref="@if (empty($modal_id)){{ 'modal-dtl' }}@else{{ $modal_id }}@endif">
    {{-- サイズ：modal-xl --}}
    <div
        class="modal-dialog modal-dialog-scrollable
        @if (isset($modal_send_confirm) && ($modal_send_confirm)) modal-dialog-centered @endif
        @hasSection('modal-size') @yield('modal-size') @else {{'modal-lg'}} @endif">
        <div class="modal-content">

            {{-- タイトル --}}
            @if (!isset($modal_send_confirm) || (!$modal_send_confirm)) 
            <div class="modal-header">
                <h4 class="modal-title">
                    @hasSection('modal-title')
                    @yield('modal-title')
                    @else
                    詳細
                    @endif
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            {{-- 本文 --}}
            <div class="modal-body">
                {{-- モーダルでフォームを表示 --}}
                @if (isset($modal_form) && ($modal_form)) 
                <div id="app-form-modal">
                @endif

                @yield('modal-body')

                @if (isset($modal_form) && ($modal_form)) 
                </div>
                @endif
            </div>

            {{-- modal-buttons: 選択モーダル・フォームモーダルの場合のVueのインスタンス --}}
            <div id="modal-buttons" class="modal-footer flex-end">
                {{-- 追加のボタンを配置 --}}
                @yield('modal-button')

                {{-- モーダルでフォームを表示 --}}
                @if (isset($modal_form) && ($modal_form)) 
                <button type="button" class="btn btn-primary" v-on:click="modalOk">確定</button>
                @endif

                @if (!isset($modal_send_confirm) || (!$modal_send_confirm)) 
                <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                @else
                {{-- 送信確認用のダイアログの場合 --}}
                <button type="button" class="btn btn-primary" v-on:click="modalOk">@if (empty($caption_OK)) OK @else{{ $caption_OK }}@endif</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
                @endif
            </div>

            @yield('modal-footer')
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
