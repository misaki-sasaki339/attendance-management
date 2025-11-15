<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WorkController as BaseWorkController;
use App\Models\Work;

class WorkController extends BaseWorkController
{
    // 勤怠情報の取得
    public function index(Request $request)
    {
        $staffId = Auth::id();
        $month = $request->input('month');

        $works = $this->getMonthlyWorks($staffId, $month);

        return view('staff.works.index', compact('works', 'month'));
    }

    // 出勤打刻機能
    public function clockIn()
    {
        $work = Work::firstOrCreate([
            'staff_id' => Auth::id(),
            'work_date' => today(),
        ]);

        $work->clockIn();

        return back();
    }

    // 退勤打刻
    public function clockOut()
    {
        $work = Work::where('staff_id', Auth::id())
            ->whereDate('work_date', today())
            ->firstOrFail();

        $work->clockOut();

        return back();
    }
}

