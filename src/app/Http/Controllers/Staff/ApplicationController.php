<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\BaseApplicationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ApplicationController extends BaseApplicationController
{
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
