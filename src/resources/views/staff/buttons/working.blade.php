<div class="button-wrapper">
    <form action="{{ route('attendance.clockOut') }}" method="POST">
        @csrf
        <button type=submit class="attendance-button attendance-button--work-out">退勤</button>
    </form>

    <form action="{{ route('attendance.breakStart') }}" method="POST">
        @csrf
        <button type=submit class="attendance-button attendance-button--break-start">休憩入</button>
    </form>
</div>
