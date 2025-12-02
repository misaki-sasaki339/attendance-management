@php
    $prev = $month->copy()->subMonth()->format('Y-m');
    $next = $month->copy()->addMonth()->format('Y-m');
@endphp

@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/works/index.css') }}">
@endsection

@section('content')
<div class="container">
    @if (auth()->user()->isAdmin() && isset($staff))
        <h2>{{ $staff->name }}さんの勤怠</h2>
    @else
        <h2>勤怠一覧</h2>
    @endif

    <div class="month-selector">
        @if (auth()->user()->isAdmin() && isset($staff))
            <a href="{{ route('admin.staffMonthly', ['id' => $staff->id, 'month' => $prev]) }}" class="calender-btn">←前月</a>
            <span class="current-month">
                <img src="{{ asset('img/カレンダー.png') }}" alt="カレンダー">
                {{ $month->format('Y/m') }}
            </span>
            <a href="{{ route('admin.staffMonthly', ['id' => $staff->id, 'month' => $next]) }}" class="calender-btn">→翌月</a>
        @else
            <a href="{{ route('attendance.index', ['month' => $prev]) }}" class="calender-btn">←前月</a>
            <span class="current-month">
                <img src="{{ asset('img/カレンダー.png') }}" alt="カレンダー">
                {{ $month->format('Y/m') }}
            </span>
            <a href="{{ route('attendance.index', ['month' => $next]) }}" class="calender-btn">→翌月</a>
        @endif
    </div>

    <table class="attendance-table">
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @foreach($works as $work)
        <tr>
            <td>{{ \Carbon\Carbon::parse($work->work_date)->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>

            @if ($work->id)
            <td>{{ $work->clock_in ? $work->clock_in->format('H:i') : ' ' }}</td>
            <td>{{ $work->clock_out ? $work->clock_out->format('H:i') : ' ' }}</td>
            <td>{{ $work->break_time }}</td>
            <td>{{ $work->work_time }}</td>
            <td>
                @if (auth()->user()->isAdmin() && isset($staff))
                <a class="work-detail" href="{{ route('admin.edit', ['id' => $work->id]) }}">詳細</a>
                @else
                <a class="work-detail" href="{{ route('attendance.edit', ['id' => $work->id]) }}">詳細</a>
                @endif
            </td>
            @else
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            @endif
        </tr>
        @endforeach
    </table>

    @if (auth()->user()->isAdmin())
        <a href="{{ route('admin.staffMonthly.csv', ['id' => $staff->id, 'month' => $month->format('Y-m')]) }}">CSV出力</a>
    @endif
</div>
@endsection

