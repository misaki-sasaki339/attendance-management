<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseWorkController;
use App\Models\Work;
use App\Models\Application;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class WorkController extends BaseWorkController
{
    // 打刻画面の表示
    public function today()
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

    // 勤怠情報(月次)の取得
    public function index(Request $request)
    {
        $staffId = Auth::id();
        $monthString = $request->input('month');
        $targetMonth = $monthString ? Carbon::parse($monthString) : now();

        $days = $this->getMonthlyAttendance($staffId, $targetMonth);

        return view('works.index', [
            'works' => $days,
            'month' => $targetMonth,
        ]);
    }

    // 勤怠詳細の取得
    public function edit($id)
    {
        $work = $this->findWorkWithRelations($id);

        // 本人・管理者以外からのアクセス禁止
        if (Auth::id() !== $work->staff_id && !$work->staff->isAdmin()) {
            abort(403);
        }

        $application = $work->application;

        $displayWork = $application ? $application->work : $work;

        $isReadonly = $application !== null;
        $isApproved = $application && $application->approval === 1;

        $breaks = $displayWork->breakTimes->isEmpty()
            ? collect([new \App\Models\BreakTime()])
            : $displayWork->breakTimes;

        return view('works.detail', [
            'work' => $displayWork,
            'breaks' => $breaks,
            'isReadonly' => $isReadonly,
            'fromApplication' => $application ? true : false,
            'application' => $application,
            'isApproved' => $isApproved,
        ]);
    }

    // 勤怠詳細の修正
    public function store(Request $request, $id)
    {
        $work = Work::findOrFail($id);

        // 本人以外からのアクセス禁止
        if (Auth::id() !== $work->staff_id) {
            abort(403);
        }

        // 承認待ちの場合更新禁止
        if ($work->application) {
            return back()->with('message', '※承認待ちのため修正はできません。');
        }

        // 出勤・退勤時間をCarbonに変換
        $newClockIn = $request->clock_in
            ? Carbon::parse($work->work_date->format('Y-m-d') . ' ' . $request->clock_in)
            : null;

        $newClockOut = $request->clock_out
            ? Carbon::parse($work->work_date->format('Y-m-d') . ' ' . $request->clock_out)
            : null;

        // 休憩をJSON型に整形
        $breaks = [];
        if($request->break_start) {
            foreach ($request->break_start as $i => $start) {
                $end = $request->break_end[$i] ?? null;

                if ($start || $end) {
                    $breaks[] = [
                        'start' => $start,
                        'end' => $end,
                    ];
                }
            }
        }
        $application = Application::updateOrCreate(
            ['work_id'=> $work->id],
            [
                'new_clock_in' => $newClockIn,
                'new_clock_out' => $newClockOut,
                'new_break_times' => json_encode($breaks),
                'reason' => $request->reason,
                'status' => 'pending',
            ],
        );

        return redirect()->route('attendance.edit', $work->id)->with('message', '修正申請を送信しました');

    }
}

