<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseWorkController;
use Carbon\Carbon;
use App\Models\Work;
use App\Models\Staff;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\WorkRequest;

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

    // 勤怠詳細の取得
    public function edit($id)
    {
        $work = $this->findWorkWithRelations($id);
        $application = $work->application;

        // 申請内容がある場合は application.work を優先して表示
        $displayWork = $application ? $application->work : $work;

        // readonly 判定：申請がある場合は編集不可
        $isReadonly = $application !== null;

        // 承認済み判定
        $isApproved = $application && $application->approval === 1;

        // break 一覧：申請内容があれば申請側 breakTimes を使う
        $breaks = $displayWork->breakTimes;

        return view('works.detail', [
            'work' => $displayWork,
            'breaks' => $breaks,
            'isReadonly' => $isReadonly,
            'fromApplication' => $application ? true : false,
            'application' => $application,
            'isApproved' => $isApproved,
            'isAdmin' => true,
        ]);
    }

    // スタッフの勤怠を直接修正
    public function update(WorkRequest $request, $id)
    {
        $work = Work::with('breakTimes')->findOrFail($id);

        $work->clock_in = $request->clock_in;
        $work->clock_out = $request->clock_out;
        $work->reason = $request->reason;
        $work->save();

        $work->breakTimes()->delete();
        foreach ($request->break_start as $i => $start) {
            if ($start || $request->break_end[$i]) {
                $work->breakTimes()->create([
                    'break_start' => $start,
                    'break_end' => $request->break_end[$i] ?? null,
                ]);
            }
        }
        return redirect()->route('admin.index')->with('flash_message', '勤怠情報を修正しました')->with('flash_type', 'success');
        ;
    }

    // スタッフ一覧の表示
    public function staffList()
    {
        $staffs = Staff::notAdmin()
            ->orderBy('id', 'asc')
            ->get();
        return view('admin.staff-list', compact('staffs'));
    }

    // スタッフの勤怠情報(月次)の取得
    public function staffMonthly(Request $request, $id)
    {
        $monthString = $request->input('month');
        $targetMonth = $monthString ? Carbon::parse($monthString) : now();

        $days = $this->getMonthlyAttendance($id, $targetMonth);

        return view('works.index', [
            'works' => $days,
            'month' => $targetMonth,
            'staff' => Staff::notAdmin()->findOrFail($id),
        ]);
    }

    // スタッフの月次勤怠をCSVエクスポート
    public function exportMonthly(Request $request, $staffId)
    {
        $month = $request->input('month');
        $targetMonth = $month ? Carbon::parse($month) : now();

        $days = $this->getMonthlyAttendance($staffId, $targetMonth);

        $response = new StreamedResponse(function () use ($days) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['日付','出勤', '退勤', '休憩', '合計']);

            foreach ($days as $day) {
                $noWork = is_null($day->clock_in) && is_null($day->clock_out);

                $workDate = $day->work_date
                    ? Carbon::parse($day->work_date)->format('Y-m-d')
                    : '';

                $clockIn = (!$noWork && $day->clock_in)
                    ? Carbon::parse($day->clock_in)->format('H:i')
                    : '';

                $clockOut = (!$noWork && $day->clock_out)
                    ? Carbon::parse($day->clock_out)->format('H:i')
                    : '';

                $break = $noWork ? '' : ($day->break_time ?? '');
                $total = $noWork ? '' : ($day->work_time ?? '');

                fputcsv($handle, [
                    $workDate,
                    $clockIn,
                    $clockOut,
                    $break,
                    $total,
                ]);
            }
            fclose($handle);
        });

        $staff = Staff::findOrFail($staffId);
        $fileName = "{$staff->name}_{$targetMonth->format('Y年m月')}度勤怠一覧.csv";
        $response->headers->set('cContent-Type', 'text/csv');
        $response->headers->set(
            'Content-Disposition',
            "attachment; filename=\"{$fileName}\""
        );

        return $response;
    }
}
