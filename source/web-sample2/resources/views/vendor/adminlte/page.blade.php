@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@if($layoutHelper->isLayoutTopnavEnabled())
@php( $def_container_class = 'container' )
@else
@php( $def_container_class = 'container-fluid' )
@endif

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation --}}
        @if($layoutHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        <div class="content-wrapper {{ config('adminlte.classes_content_wrapper', '') }}">

            {{-- Content Header --}}
            <div class="content-header">
                <div class="{{ config('adminlte.classes_content_header') ?: $def_container_class }}">
                    {{-- タイトルの表示。子ページであれば表示 --}}
                    @hasSection('child_page')
                    <div class="row">
                        <div class="col-sm-6">
                            @hasSection('page_title')
                            <h1>@yield('page_title')</h1><br>
                            @else
                            <h1>@yield('title')</h1><br>
                            @endif
                        </div>

                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                {{-- 一覧は固定 --}}
                                <li class="breadcrumb-item">
                                    @hasSection('base_page')
                                    <a href="@yield('base_page')">@yield('base_page_title')</a>
                                    @else
                                    <a href="{{ Request::root()}}/{{ Request::segment(1) }}">
                                        @hasSection('base_page_title')
                                        @yield('base_page_title')
                                        @else
                                        一覧
                                        @endif
                                    </a>
                                    @endif
                                </li>
                                {{-- 三階層目のページの場合 --}}
                                @hasSection('parent_page')
                                <li class="breadcrumb-item">
                                    <a href="@yield('parent_page')">@yield('parent_page_title')</a>
                                </li>
                                @endif
                                {{-- 四階層目のページの場合 --}}
                                @hasSection('parent_page2')
                                <li class="breadcrumb-item">
                                    <a href="@yield('parent_page2')">@yield('parent_page_title2')</a>
                                </li>
                                @endif
                                {{-- タイトル --}}
                                <li class="breadcrumb-item active">@yield('title')</li>
                            </ol>
                        </div>
                    </div>
                    @else
                        @hasSection('page_title')
                            <h1>@yield('page_title')</h1><br>
                        @else
                            <h1>@yield('title')</h1><br>
                        @endif
                    @endif

                </div>
            </div>

            {{-- Main Content --}}
            <div class="content">
                <div class="{{ config('adminlte.classes_content') ?: $def_container_class }}">
                    @yield('content')
                </div>
            </div>

            {{-- 画面が長いときに隙間ができるので対応 --}}
            &nbsp;
        </div>

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
