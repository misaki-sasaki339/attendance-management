@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-container">
    <p class="message">登録していただいたメールアドレスに認証メールを送付しました。<br />
        メール認証を完了してください。</p>

    {{--認証確認ボタン--}}
    <a href="" target="_blank" class="form__button-submit">
        メール認証はこちら
    </a>

    {{--メール再送ボタン--}}
    <form method="POST" action="{{ route('verification.send') }}" class="form-resend">
        @csrf
        <button class="form__link" type="submit">認証メールを再送する</button>
    </form>
</div>
@endsection
