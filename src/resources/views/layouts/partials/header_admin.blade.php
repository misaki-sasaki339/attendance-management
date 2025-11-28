<nav class="nav-admin">
    <ul>
        <li><a href="{{ route('admin.index') }}">勤怠一覧</a></li>
        <li><a href="{{ route('admin.staffList') }}">スタッフ一覧</a></li>
        <li><a href="{{ route('admin.application.index') }}">申請一覧</a></li>
        <li>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="logout-btn">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
