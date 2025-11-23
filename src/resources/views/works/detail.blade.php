@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/detail.css') }}">
@endsection

@section('content')
<div class="container">
    <h2>勤怠詳細</h2>

    @if ($isReadonly)
        @include('works.partials._works_fields_readonly', [
            'work' => $work,
            'breaks' => $breaks
        ])
        <p class="readonly-message">＊承認待ちのため修正できません。</p>

    @else
        @if(auth()->user()->isAdmin())
            @include('works.partials._admin_actions', ['work' => $work,'breaks' => $breaks] )
        @else
            @include('works.partials._staff_actions', ['work'=> $work, 'breaks' => $breaks])
        @endif
    @endif
</div>
@endsection
