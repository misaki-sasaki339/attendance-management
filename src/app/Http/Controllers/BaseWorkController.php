<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Work;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonPeriod;
use App\Models\Staff;

class BaseWorkController extends Controller
{
    /**
     * 月次勤怠一覧を取得（管理者/スタッフ共通）
     *
     * @param int|null $staffId 指定があればそのスタッフの勤怠を取得
     * @param string|null $month 例: '2025-11' 指定がなければ今月
     */

    protected function getMonthlyWorks(?int $staffId = null, ?string $month = null)
    {
        //対象の月を指定、なければ今月
        $targetMonth = $month
            ? Carbon::parse($month)
            : Carbon::now();

        // Workに紐づく休憩、申請のデータを事前取得 月の勤務データを昇順で並べ替え
        $query = Work::with(['breakTimes', 'application.approval'])
            ->whereMonth('work_date', $targetMonth->month)
            ->whereYear('work_date', $targetMonth->year)
            ->orderBy('work_date', 'asc');

        // staffIdがあればそのidに絞る
        if (!is_null($staffId)) {
            $query->where('staff_id', $staffId);
        }

        return $query->get();
    }

    // 勤怠に紐づく申請・休憩データを取得
    protected function findWorkWithRelations($id)
    {
        return Work::with(['application.approval', 'breakTimes'])->findOrFail($id);
    }

    // 月次勤怠の取得
    protected function getMonthlyAttendance(int $staffId, Carbon $targetMonth)
    {
        $works = $this->getMonthlyWorks($staffId, $targetMonth);

        $period = CarbonPeriod::create(
            $targetMonth->copy()->startOfMonth(),
            $targetMonth->copy()->endOfMonth(),
        );

        $days = collect();

        /** @var \Carbon\Carbon $date */
        foreach ($period as $date) {
            $work = $works->first(fn($w) => $w->work_date->isSameDay($date));

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
        return $days;
    }
}
