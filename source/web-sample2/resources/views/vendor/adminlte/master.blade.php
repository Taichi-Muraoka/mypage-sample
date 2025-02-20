<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_css_path', 'css/app.css')) }}">
            @break

            @case('vite')
                @vite([config('adminlte.laravel_css_path', 'resources/css/app.css'), config('adminlte.laravel_js_path', 'resources/js/app.js')])
            @break

            @case('vite_js_only')
                @vite(config('adminlte.laravel_js_path', 'resources/js/app.js'))
            @break

            @default
                <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

                @if(config('adminlte.google_fonts.allowed', true))
                    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
                @endif
        @endswitch
        {{-- Extra Configured Plugins Stylesheets --}}
        @include('adminlte::plugins', ['type' => 'css'])

        <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

        {{-- メインCSS --}}
        @vite('resources/sass/app.scss')
    @endif

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('./favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
    {{-- ジェネレーターで作成 --}}
    <meta name="msapplication-square70x70logo" content="{{ asset('favicons/site-tile-70x70.png') }}">
    <meta name="msapplication-square150x150logo" content="{{ asset('favicons/site-tile-150x150.png') }}">
    <meta name="msapplication-wide310x150logo" content="{{ asset('favicons/site-tile-310x150.png') }}">
    <meta name="msapplication-square310x310logo" content="{{ asset('favicons/site-tile-310x310.png') }}">
    <meta name="msapplication-TileColor" content="#0078d7">

    <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}">
    <link rel="icon" href="{{ asset('favicons/favicon.ico') }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-touch-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-touch-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-touch-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-touch-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-touch-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-touch-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-touch-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-touch-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-touch-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="36x36" href="{{ asset('favicons/android-chrome-36x36.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicons/android-chrome-48x48.png') }}">
    <link rel="icon" type="image/png" sizes="72x72" href="{{ asset('favicons/android-chrome-72x72.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/android-chrome-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicons/android-chrome-128x128.png') }}">
    <link rel="icon" type="image/png" sizes="144x144" href="{{ asset('favicons/android-chrome-144x144.png') }}">
    <link rel="icon" type="image/png" sizes="152x152" href="{{ asset('favicons/android-chrome-152x152.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-chrome-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('favicons/android-chrome-256x256.png') }}">
    <link rel="icon" type="image/png" sizes="384x384" href="{{ asset('favicons/android-chrome-384x384.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicons/android-chrome-512x512.png') }}">
    <link rel="icon" type="image/png" sizes="36x36" href="{{ asset('favicons/icon-36x36.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicons/icon-48x48.png') }}">
    <link rel="icon" type="image/png" sizes="72x72" href="{{ asset('favicons/icon-72x72.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicons/icon-128x128.png') }}">
    <link rel="icon" type="image/png" sizes="144x144" href="{{ asset('favicons/icon-144x144.png') }}">
    <link rel="icon" type="image/png" sizes="152x152" href="{{ asset('favicons/icon-152x152.png') }}">
    <link rel="icon" type="image/png" sizes="160x160" href="{{ asset('favicons/icon-160x160.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="196x196" href="{{ asset('favicons/icon-196x196.png') }}">
    <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('favicons/icon-256x256.png') }}">
    <link rel="icon" type="image/png" sizes="384x384" href="{{ asset('favicons/icon-384x384.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicons/icon-512x512.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/icon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="24x24" href="{{ asset('favicons/icon-24x24.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/icon-32x32.png') }}">
    <link rel="manifest" href="{{ asset('manifest.icon.json') }}">
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <script src="{{ mix(config('adminlte.laravel_js_path', 'js/app.js')) }}"></script>
            @break

            @case('vite')
            @case('vite_js_only')
            @break

            @default
                <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
                <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
                <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
                <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
        @endswitch
    @endif

    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Webアプリの設定を保持 --}}
    <script>
        appInfo = { path:'{{ Request::path() }}', root:'{{ Request::root() }}', view: '{{ $view_name }}' 
        {{-- 三階層目のページの場合。親のURLを保持しておく。戻るボタンに使用する --}}
        @hasSection('parent_page')
        , parent: '@yield("parent_page")'
        @endif
        {{-- 四階層目のページの場合。親のURLを保持しておく。戻るボタンに使用する --}}
        @hasSection('parent_page')
        , parent2: '@yield("parent_page2")'
        @endif
        {{-- 他機能の画面からの遷移の場合。遷移元のURLを保持しておく。戻るボタンに使用する --}}
        @hasSection('base_page')
        , base: '@yield("base_page")'
        @endif
        };
    </script>

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

    {{-- メインJS --}}
    @vite('resources/js/app.js')

</body>

</html>
