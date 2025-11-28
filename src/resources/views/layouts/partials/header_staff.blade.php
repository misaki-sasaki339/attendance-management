<nav class="nav-staff">
    <ul>
        <li><a href="{{ route('attendance.today') }}">勤怠</a></li>
        <li><a href="{{ route('attendance.index') }}">勤怠一覧</a></li>
        <li><a href="{{ route('staff.application.index') }}">申請</a></li>
        <li>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="logout-btn">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
