@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
<div class="container">
    <h2>{{ $title }}</h2>

    <form action="{{ route($route) }}" class="form" method="POST">
    @csrf
    <div class="form-group">
        <label for="email">メールアドレス</label>
        <input class="form__input @error('email') is-invalid @enderror" type="text" name="email" id="email" value="{{ old('email') }}">
        @error('email')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="email">パスワード</label>
        <input class="form__input @error('password') is-invalid @enderror" type="password" name="password" id="password">
        @error('password')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit">{{ $buttonLabel }}</button>
    </form>

    {{-- スタッフのログイン画面だけに表示 --}}
    @if($role === 'staff')
    <p class="register-link">
        <a href="{{ route('register') }}">会員登録はこちら</a>
    </p>
    @endif
</div>
@endsection


