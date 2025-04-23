{{------------------------------------------ 
    button リスト 選択モーダル
  --------------------------------------------}}

{{--
  vueDataAttr: data属性を定義(vue)
--}}
@props(['vueDataAttr' => []])

<button type="button" 
  class="btn btn-sm btn-secondary" 
  data-toggle="modal"
  
  {{-- クリックイベント --}}
  v-on:click="selectedData"

  {{-- 開くモーダルを指定。動的に指定する場合は、vueDataAttr=['target' => 'xxx'] のように指定するのでそれ以外の場合 --}} 
  @if (!isset($vueDataAttr['target']))
  data-target="@if (empty($dataTarget)){{ '#modal-dtl' }}@else{{ $dataTarget }}@endif" 
  @endif

  {{-- buttonに対するdata属性の定義。vueで取得する用。 --}}
  @foreach($vueDataAttr as $key => $val)
   :data-{{$key}}="{{ $vueDataAttr[$key] }}"
  @endforeach
>
選択
</button>

