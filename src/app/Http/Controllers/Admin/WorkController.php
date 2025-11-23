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

    // 勤怠詳細の取得
    public function edit($id)
    {
        $work = $this->findWorkWithRelations($id);
        $isReadonly = $work->isPending();
        $breaks = $work->breakTimes;

    // 修正申請中のときは「閲覧専用画面」に切り替える
    if ($isReadonly) {
        return view('works.detail', [
            'work' => $work,
            'breaks' => $breaks,
            'isReadonly' => $isReadonly,
        ]);
    }

        // ない場合は編集画面へ
        return view('works.detail', [
            'work' => $work,
            'breaks' => $breaks,
            'isReadonly' => $isReadonly,
        ]);
    }
    
    public function update(Request $request, $id)
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
        return redirect()->route('admin.edit', $id)->with('success', '勤怠を更新しました');
    }
}
