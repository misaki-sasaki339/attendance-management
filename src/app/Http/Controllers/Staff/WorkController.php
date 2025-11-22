<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WorkController as BaseWorkController;
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

    // 勤怠情報の取得
    public function index(Request $request)
    {
        $staffId = Auth::id();
        $monthString = $request->input('month');

        // mont指定の場合はCarbonにパース、指定ない場合は今月
        $targetMonth = $monthString ? Carbon::parse($monthString) : now();

        $works = $this->getMonthlyWorks($staffId, $targetMonth);

        $period = CarbonPeriod::create(
            $targetMonth->copy()->startOfMonth(),
            $targetMonth->copy()->endOfMonth(),
        );

        $days = collect();

        /** @var \Carbon\Carbon $date */
        foreach ($period as $date) {
            $work = null;

            foreach ($works as $w) {
                if ($w->work_date->isSameDay($date)) {
                    $work = $w;
                    break;
                }
            }

            if (!$work) {
                $work = new Work([
                    'id' => null,
                    'work_date' => $date->copy(),
                    'clock_in' => null,
                    'clock_out' => null,
                ]);
            }
            $days->push($work);
        }

        return view('staff.index', ['works'=>$days, 'month'=>$targetMonth]);
    }

    // 勤怠詳細の取得
    public function edit($id)
    {
        $work = Work::findOrFail($id);
        $this->ensureOwner($work);

        // 修正申請がある場合は閲覧専用画面へ飛ばす
        if ($work->application) {
            return view('staff.pending', compact('work'));
        }

        // ない場合は編集画面へ
        return view('staff.edit', compact('work'));
    }

    // 勤怠詳細の修正
    public function update(Request $request, $id)
    {
        $work = Work::findOrFail($id);
        $this->ensureOwner($work);

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
                'new_break_times' => $breaks,
                'reason' => $request->reason,
                'status' => 'pending',
            ],
        );

        return redirect()->route('attendance.index')->with('message', '修正申請を送信しました');

    }
}

