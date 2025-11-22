<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\WorkController as BaseWorkController;
use Carbon\Carbon;
use App\Models\Work;

class WorkController extends BaseWorkController
{
    // 当日の出勤スタッフ一覧の表示
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $works = Work::with(['staff', 'breakTimes'])
            ->whereDate('work_date', $date->format('Y-m-d'))
            ->orderBy('work_date', 'asc')
            ->get();

        return view('admin.index', [
            'date' => $date,
            'prev' => $date->copy()->subDay()->format('Y-m-d'),
            'next' => $date->copy()->addDay()->format('Y-m-d'),
            'works' => $works,
        ]);
    }

    // 勤怠情報の取得
    public function xxx(Request $request)
    {
        $staffId = $request->input('staff_id');
        $month = $request->input('month');

        $works = $this->getMonthlyWorks($staffId, $month);

        return view('admin.works.index', compact('works', 'month'));
    }
}
