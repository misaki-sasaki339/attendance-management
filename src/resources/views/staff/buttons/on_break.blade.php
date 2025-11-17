<form action="{{ route('attendance.breakEnd') }}" method="POST">
    @csrf
    <button type=submit class="attendance-button attendance-button--break-end">休憩戻</button>
</form>
