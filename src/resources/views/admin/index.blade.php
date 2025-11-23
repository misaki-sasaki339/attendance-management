@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/index.css') }}">
@endsection

@section('content')
<div class="container">
    <h2>{{ $date->format('Y年m月d日の勤怠') }}</h2>

    <div class="date-selector">
        <a href="{{ route('admin.index', ['date' => $prev]) }}" class="calender-btn">←前日</a>
        <span class="today"><img src="{{ asset('img/カレンダー.png') }}" alt="カレンダー">{{ $date->format('Y/m/d') }}</span>
        <a href="{{ route('admin.index', ['date' => $next]) }}" class="month-btn">→翌日</a>
    </div>

    <table class="attendance-table">
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @foreach($works as $work)
        <tr>
            <td>{{ $work->staff->name }}</td>
            <td>{{ $work->clock_in->format('H:i') }}</td>
            <td>{{ $work->clock_out->format('H:i') }}</td>
            <td>{{ $work->break_time }}</td>
            <td>{{ $work->work_time }}</td>
            <td><a class="work-detail" href="{{ route('admin.edit', ['id' => $work->id]) }}"">詳細</a></td>
        </tr>
        @endforeach
    </table>
</div>
@endsection
