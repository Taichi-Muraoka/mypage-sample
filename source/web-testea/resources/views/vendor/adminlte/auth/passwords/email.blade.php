@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'パスワードリセット')

@php( $password_email_url = View::getSection('password_email_url') ?? config('adminlte.password_email_url',
'password/email') )

@if (config('adminlte.use_route_url', false))
@php( $password_email_url = $password_email_url ? route($password_email_url) : '' )
@else
@php( $password_email_url = $password_email_url ? url($password_email_url) : '' )
@endif

@section('auth_header', __('adminlte::adminlte.password_reset_message'))

@section('auth_body')

@if(session('status'))
<div class="callout callout-info">
    <p>{{ session('status') }}</p>
</div>
@endif

<form action="{{ $password_email_url }}" method="post">
    {{ csrf_field() }}

    {{-- Email field --}}
    <div class="input-group mb-3">
        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
            value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" autocomplete="off" autofocus>
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
        </div>
        @if($errors->has('email'))
        <div class="invalid-feedback">
            {{ $errors->first('email') }}
        </div>
        @endif
    </div>

    {{-- Send reset link button --}}
    <button type="submit" class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
        <span class="fas fa-share-square"></span>
        {{-- __('adminlte::adminlte.send_password_reset_link') --}}
        リセットリンクを送信
    </button>

</form>

@stop

@section('auth_footer')
<p class="my-0">
    <a href="{{ route('login') }}">
        ログインへ戻る
    </a>
</p>
@stop
