<form action="{{ route('attendance.clockIn') }}" method="POST">
    @csrf
    <button type=submit>出勤</button>
</form>
