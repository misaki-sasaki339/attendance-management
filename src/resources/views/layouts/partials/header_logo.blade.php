<div class="header-logo__img">
    <a
        href="
        @guest
{{ route('staff.login') }}
        @else
            @if (auth()->user()->isAdmin())
                {{ route('admin.index') }}
            @else
                {{ route('attendance.today') }}
            @endif @endguest
    ">
        <img class="header-logo__img" src="{{ asset('img/logo.png') }}" alt="COATCHTECHロゴ">
    </a>
</div>
