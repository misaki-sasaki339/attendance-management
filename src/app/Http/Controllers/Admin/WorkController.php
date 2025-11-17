<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\WorkController as BaseWorkController;

class WorkController extends BaseWorkController
{
    // 勤怠情報の取得
    public function xxx(Request $request)
    {
        $staffId = $request->input('staff_id');
        $month = $request->input('month');

        $works = $this->getMonthlyWorks($staffId, $month);

        return view('admin.works.index', compact('works', 'month'));
    }
}
