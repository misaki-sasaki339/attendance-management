<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/layouts/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
    @yield('css')
    <title>COATCHTECH</title>
</head>
<body>

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
        @else
            @include('layouts.partials.header_staff')
        @endif
    @endif

    <main>
    @yield('content')
    </main>

</body>
</html>
