@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/works/detail.css') }}">
@endsection

@section('content')
<div class="container">
    <h2>勤怠詳細</h2>

    {{-- ================================
        ★ 申請内容（スタッフ・管理者共通）
         ================================ --}}
    @if ($work->hasApplication())
        @include('works.partials._works_requested', ['application' => $application])
    @endif

    {{-- ================================
         ★ 承認待ち（pending）
         管理者：申請一覧 → 承認ボタン最優先
         ================================= --}}
    @if ($work->isPending() && auth()->user()->isAdmin() && $fromApplication)
        <form method="POST" action="{{ route('admin.application.approve', ['id' => $application->id]) }}">
            @csrf
            <button class="submit-btn">承認</button>
        </form>
    @endif

    {{-- ================================
         ★ 承認待ちメッセージ
         ・スタッフ：常に表示
         ・管理者：勤怠一覧から来たときのみ表示
         ・管理者：申請一覧から来たときは非表示
     ================================= --}}
    @if ($work->isPending() && !($fromApplication && auth()->user()->isAdmin()))
        @include('works.partials._readonly-message')
    @endif

    {{-- ================================
         ★ スタッフ：修正申請前のみ入力可
         ================================ --}}
    @if (!$work->hasApplication() && !auth()->user()->isAdmin())
        <form method="POST" action="{{ route('application.store', $work->id ) }}">
            @csrf
            <input type="hidden" name="work_id" value="{{ $work->id }}">
            @include('works.partials._works_fields', ['work' => $work, 'breaks' => $breaks])
            <button type="submit">修正</button>
        </form>
    @endif

    {{-- ================================
         ★ 管理者：修正申請前のみ直接修正可
         ================================ --}}
    @if (!$work->hasApplication() && auth()->user()->isAdmin())
        <form method="POST" action="{{ route('admin.update', $work->id) }}">
            @csrf
            @method('PUT')
            @include('works.partials._works_fields', ['work' => $work, 'breaks' => $breaks])
            <button type="submit">修正</button>
        </form>
    @endif

    {{-- ================================
        ★ 承認済み（スタッフ・管理者共通）
         ================================ --}}
    @if ($work->isApproved())
        @include('works.partials._approved-message')
    @endif
</div>
@endsection
