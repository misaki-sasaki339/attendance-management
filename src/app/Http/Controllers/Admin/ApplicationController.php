<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseApplicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends BaseApplicationController
{
    // 修正申請の一覧を表示
    public function index(Request $request)
    {
        $result = $this->getApplications($request);
        return view('works.application', [
            'applications' => $result['applications'],
            'tab' => $result['tab'],
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
            'isAdmin' => true,
            'isApproved' => $application && $application->approval === 1,
        ]));
    }

    // 承認機能
    public function approve($attendance_correct_request_id)
    {
        // 申請データの取得
        $application = $this->getApplicationDetail($attendance_correct_request_id);
        $work = $application->work;

        // 承認レコード作成
        $application->approval()->create([
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Applicationsテーブルのstatus更新
        $application->update([
            'status' => 'approved',
        ]);

        // workへ申請内容反映
        $work->update([
            'clock_in' => $application->new_clock_in ?? $work->clock_in,
            'clock_out' => $application->new_clock_out ?? $work->clock_out,
            'reason' => $application->reason,
        ]);

        // 休憩修正
        if (!empty($application->new_break_times)) {
            $work->breakTimes()->delete();
            $breaks = json_decode($application->new_break_times, true);
            foreach ($breaks as $break) {
                $work->breakTimes()->create([
                    'break_start' => $break['start'],
                    'break_end' => $break['end'],
                ]);
            }
        }
        return redirect()->route('admin.application.index')->with('flash_message', '申請を承認しました')->with('flash_type', 'success');
    }
}
