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

    <main>
    @yield('content')
    </main>

</body>
</html>
