<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class BaseApplicationController extends Controller
{
    // 申請一覧の取得
    protected function getApplications(Request $request, $staffId = null)
    {
        $tab = $request->query('tab', 'pending');

        $query = Application::with([
            'work',
            'work.staff',
            'approval'
        ]);

        // スタッフは自分の申請分だけ取得
        if ($staffId) {
            $query->whereHas(
                'work',
                fn ($q) =>
                $q->where('staff_id', $staffId)
            );
        }

        // タブ別の絞り込み
        if ($tab === 'approved') {
            $query->whereHas('approval');
        } else {
            $query->whereDoesntHave('approval');
        }
        return [
            'applications' => $query->orderBy('created_at', 'desc')->get(),
            'tab' => $tab,
        ];
    }

    // 申請詳細の取得
    protected function getApplicationDetail($id)
    {
        return Application::with([
            'work',
            'work.breakTimes',
            'work.staff',
            'approval'
        ])->findOrFail($id);
    }

    // 申請詳細で渡すフラグ
    protected function getApplicationShowData($id)
    {
        $application = $this->getApplicationDetail($id);

        return [
            'application'      => $application,
            'work'             => $application->work,
            'breaks'           => $application->work->breakTimes,
            'requestedBreaks'  => json_decode($application->new_break_times, true) ?? [],
        ];
    }
}
