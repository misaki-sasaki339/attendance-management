@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register-container">
    <h2>会員登録</h2>

    <form action="{{ route('register') }}" class="form" method="POST">
    @csrf
    <div class="form-group">
        <label for="name">名前</label>
        <input class="form__input @error('name') is-invalid @enderror" type="text" name="name" id="name" value="{{ old('name') }}">
        @error('name')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="email">メールアドレス</label>
        <input class="form__input @error('email') is-invalid @enderror" type="text" name="email" id="email" value="{{ old('email') }}">
        @error('email')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="email">パスワード</label>
        <input class="form__input @error('password') is-invalid @enderror" type="password" name="password" id="password" >
        @error('password')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation">パスワード確認</label>
        <input class="form__input @error('password') is-invalid @enderror" type="password" name="password_confirmation" id="password_confirmation">
        @error('password_confirmation')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit">登録する</button>
    </form>

    <p class="login-link">
        <a href="{{ route('staff.login')}}">ログインはこちら</a>
    </p>
</div>
@endsection


