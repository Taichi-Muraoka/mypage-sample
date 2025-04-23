{{------------------------------------------
    input - file
  --------------------------------------------}}

@props(['caption' => '', 'id' => '', 'editData' => [], 'vShow' => ''])

{{-- バリデーションエラー時のスクロール先 --}}
<span class="form-validation" data-id="{{ $id }}"></span>

<div class="form-group"
  {{-- v-show --}}
  @if ($vShow)
  v-show="{{ $vShow }}"
  @endif >

  {{-- ラベル --}}
  @if (!empty($caption))
  <label for="{{ $id }}"><span class="input-title">{{ $caption }}</span></label>
  @endif

  <div class="input-group">

    <div class="custom-file">
      {{-- refはファイル一覧を取得するためのもの --}}
      <input type="file" class="custom-file-input" id="file_{{ $id }}" ref="file_{{ $id }}" multiple="multiple"
        {{-- エラー時の表示 --}}
        v-bind:class="{ 'is-invalid': form_err.class.{{ $id }} || form_err.class.file_{{ $id }} }" 
      >
      <label class="custom-file-label" for="file_{{ $id }}" data-browse="参照">ファイルを選択</label>
    </div>

    <div class="input-group-append">
      {{-- 取り消しボタンはclassで引っ掛けてクリックイベントを発動させる --}}
      <button type="button" class="btn btn-outline-secondary input-group-text inputFileReset"
      v-bind:class="{ 'error-btn': form_err.class.{{ $id }} || form_err.class.file_{{ $id }} }" 
      >取消</button>
    </div>

  </div>

  @isset($editData[$id])
  <div class="mt-1">
    <span class="fileUploaded">
      {{-- アップロード済みファイル名 --}}
      <span class="align-middle">アップロード済み:&nbsp;{{$editData[$id]}}</span>&nbsp;&nbsp;
      {{-- 削除ボタン --}}
      {{-- 機能としては作成したが、今回使わないので不要とした
      <button type="button" class="btn btn-default btn-sm" v-on:click="fileUploadedDelete">
        <i class="fas fa-trash"></i> 削除
      </button>
      --}}
    </span>

    {{-- 編集時にデータをセット --}} 
    <input type="hidden" id="{{ $id }}" v-model="form.{{ $id }}" value="{{$editData[$id]}}">
  </div>
  @endisset

  {{-- バリデート結果のエラー --}}
  <ul class="err-list" v-cloak>
    <li v-for="msg in form_err.msg.{{ $id }}">
      @{{ msg }}
    </li>
    <li v-for="msg in form_err.msg.file_{{ $id }}">
      @{{ msg }}
    </li>
  </ul>
</div>