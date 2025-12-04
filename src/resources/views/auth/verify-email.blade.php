@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email__container">
    <p class="message">登録していただいたメールアドレスに認証メールを送付しました。<br />
        メール認証を完了してください。</p>

    {{--認証確認ボタン--}}
    {{-- 開発環境では MailHog で認証メールを確認してください --}}
    <a href="http://localhost:8025" target="_blank" class="form__button-submit">
        メール認証はこちら
    </a>

    {{--メール再送ボタン--}}
    <form method="POST" action="{{ route('verification.send') }}" class="form-resend">
        @csrf
        <button class="form__link" type="submit">認証メールを再送する</button>
    </form>
</div>
@endsection
