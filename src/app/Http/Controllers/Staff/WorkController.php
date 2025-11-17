<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WorkController as BaseWorkController;
use App\Models\Work;

class WorkController extends BaseWorkController
{
    // 打刻画面の表示
    public function index()
    {
        $todayWork = Work::where('staff_id', Auth::id())
            ->whereDate('work_date', today())
            ->with('breakTimes')
            ->first();
        return view('staff.attendance', compact('todayWork'));
    }

    // 出勤打刻機能
    public function clockIn()
    {
        $work = Work::todayWork();
        $work->clockIn();
        return back();
    }

    // 退勤打刻
    public function clockOut()
    {
        $work = Work::todayWork();
        $work->clockOut();
        return back();
    }

    // 休憩入打刻
    public function breakStart()
    {
        $work = Work::todayWork();
        $work->breakTimes()->create(['break_start' => now()]);
        return back();
    }

    // 休憩終了打刻
    public function breakEnd()
    {
        $work = Work::todayWork();
        $break = $work->breakTimes()->whereNull('break_end')->latest()->firstOrFail();
        $break->update(['break_end' => now()]);
        return back();
    }

    // 勤怠情報の取得
    public function xxx(Request $request)
    {
        $staffId = Auth::id();
        $month = $request->input('month');

        $works = $this->getMonthlyWorks($staffId, $month);

        return view('staff.works.index', compact('works', 'month'));
    }
}

