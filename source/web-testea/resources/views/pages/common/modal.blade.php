{{------------------------------------------ 
    modal
  --------------------------------------------}}

{{--
    modal_id: モーダルのID
    modal_send_confirm: 送信確認用のダイアログ。fadeは無効にし、タイトル行は非表示にする
    modal-size: モーダルのサイズ
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
                @yield('modal-body')
            </div>

            <div class="modal-footer flex-end">
                {{-- 追加のボタンを配置 --}}
                @yield('modal-button')

                @if (!isset($modal_send_confirm) || (!$modal_send_confirm)) 
                <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                @else
                {{-- 送信確認用のダイアログの場合 --}}
                <button type="button" class="btn btn-primary" v-on:click="modalOk">OK</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
                @endif
            </div>

            @yield('modal-footer')
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
{{-- 選択モーダルの場合のVueのインスタンス --}}
<div id="selected-modal"></div>
<!-- /.modal -->
