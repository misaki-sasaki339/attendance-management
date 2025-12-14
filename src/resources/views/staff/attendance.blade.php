@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/staff/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance-container">

        @if ($todayWork)
            <div class="work-status">{{ $todayWork->status }}</div>

            <div class="attendance-date">
                {{ now()->format('Y年m月d日') }}
                ({{ ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek] }})
            </div>
            <div id="current-time" class="attendance-time"></div>

            @if ($todayWork->status === '勤務外')
                @include('staff.buttons.not_working')
            @elseif ($todayWork->status === '出勤中')
                @include('staff.buttons.working')
            @elseif ($todayWork->status === '休憩中')
                @include('staff.buttons.on_break')
            @elseif ($todayWork->status === '退勤済')
                @include('staff.buttons.finished')
            @endif
        @else
            {{-- 初めての出勤日 --}}
            @include('staff.buttons.not_working')
        @endif
    </div>
@endsection

<script>
    function updateClock() {
        const now = new Date();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        document.getElementById('current-time').textContent =
            `${hours}:${minutes}`;
    }

    // 1秒ごとに更新
    setInterval(updateClock, 1000);

    // 最初に即実行
    updateClock();
</script>
