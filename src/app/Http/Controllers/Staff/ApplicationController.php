<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\BaseApplicationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Work;
use Carbon\Carbon;
use App\Models\Application;
use App\Http\Requests\ApplicationRequest;

class ApplicationController extends BaseApplicationController
{
    // 勤怠詳細の修正
    public function store(ApplicationRequest $request, $id)
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
        if ($request->break_start) {
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
            ['work_id' => $work->id],
            [
                'new_clock_in' => $newClockIn,
                'new_clock_out' => $newClockOut,
                'new_break_times' => json_encode($breaks),
                'reason' => $request->reason,
                'status' => 'pending',
            ],
        );

        return redirect()->route('attendance.index')->with('flash_message', '修正申請を送信しました')->with('flash_type', 'success');

    }

    // 修正申請の一覧を表示
    public function index(Request $request)
    {
        $result = $this->getApplications($request, Auth::id());
        return view('works.application', [
            'applications' => $result['applications'],
            'tab' => $result['tab'],
            'fromApplication' => true,
            ]);
    }

    // 修正申請詳細(勤怠詳細)を表示
    public function show($id)
    {
        // Application モデルそのものを取得
        $application = $this->getApplicationDetail($id);
        // 画面表示用データ(work,breaks,requestedBreaks)の取得
        $data = $this->getApplicationShowData($id);

        return view('works.detail', array_merge($data, [
            'application' => $application,
            'fromApplication' => true,
            'isAdmin' => false,
        ]));
    }
}
