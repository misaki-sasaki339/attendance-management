<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WorkController as BaseWorkController;

class WorkController extends BaseWorkController
{
    public function index(Request $request)
    {
        $staffId = Auth::id();
        $month = $request->input('month');

        $works = $this->getMonthlyWorks($staffId, $month);

        return view('staff.works.index', compact('works', 'month'));
    }
}

