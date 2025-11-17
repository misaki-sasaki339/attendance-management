<form action="{{ route('attendance.clockIn') }}" method="POST">
    @csrf
    <button type=submit class="attendance-button attendance-button--work-start">出勤</button>
</form>
