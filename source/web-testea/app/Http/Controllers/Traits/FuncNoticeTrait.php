<?php

namespace App\Http\Controllers\Traits;

use App\Models\NoticeGroup;
use App\Models\NoticeTemplate;

/**
 * お知らせ - 機能共通処理
 */
trait FuncNoticeTrait
{
    /**
     * テンプレートメニューの取得
     *
     * @return array 定型文情報取得
     */
    private function getMenuOfNoticeTemplate()
    {
        return NoticeTemplate::select(
            'template_id',
            'template_name as value',
        )
            ->orderBy('order_code', 'asc')
            ->get()
            ->keyBy('template_id');
    }

    /**
     * 宛先グループメニューの取得
     *
     * @return array 宛先グループリスト
     */
    private function getMenuOfNoticeGroup()
    {
        return NoticeGroup::select(
            'notice_group_id',
            'group_name as value'
        )
            ->orderBy('notice_group_id', 'asc')
            ->get();
    }
}
