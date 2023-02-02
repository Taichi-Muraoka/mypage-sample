<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * データ管理 - コントローラ
 */
class DataMngController extends Controller
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    //==========================
    // 一覧
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {

        return view('pages.admin.data_mng');
    }
}
