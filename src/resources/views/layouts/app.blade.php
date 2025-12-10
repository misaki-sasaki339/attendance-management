<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/layouts/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
    @yield('css')
    <title>COATCHTECH</title>
</head>
<body>
    <header class="header">
    @include('layouts.partials.header_logo')

    {{-- 認証系の画面ではナビを表示しない --}}
    @if (request()->routeIs('register') ||
        request()->routeIs('password.*') ||
        request()->routeIs('staff.login') ||
        request()->routeIs('admin.login')
    )
        {{-- 何も表示しない --}}
    @else
        {{-- ログイン済みユーザーのナビ切り替え --}}
        @if (auth()->check() && auth()->user()->isAdmin())
            @include('layouts.partials.header_admin')
        @elseif(auth('staff')->check())
            @include('layouts.partials.header_staff')
        @endif
    @endif
    </header>

    <main>
    @yield('content')
    @if (session('flash_message'))
    <div id="flash-message"
        class="flash-message {{ session('flash_type') === 'error' ? 'flash-error' : 'flash-success' }}">
        {!! session('flash_message') !!}
    </div>
    @endif
    </main>

</body>
</html>
