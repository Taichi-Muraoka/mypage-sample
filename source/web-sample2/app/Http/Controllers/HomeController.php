<?php

namespace App\Http\Controllers;

use App\Libs\AuthEx;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // それぞれのホームへリダイレクト
        if (AuthEx::isAdmin()) {
            // 管理者向けページ
            return redirect()->route('room_calendar');
        } else {
            // 管理者以外
            return redirect()->route('notice');
        }
    }
}
