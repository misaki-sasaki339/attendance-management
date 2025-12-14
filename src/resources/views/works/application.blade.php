@php
    $tabs = [
        'pending' => '承認待ち',
        'approved' => '承認済み',
    ];
@endphp

@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/works/application.css') }}">
@endsection

@section('content')
    <div class="application-wrapper">
        <div class="application-container">
            <h2>申請一覧</h2>
            <div class="tabs">
                <ul class="tab__list">
                    @foreach ($tabs as $key => $label)
                        <li>
                            <a href="{{ request()->fullUrlWithQuery(['tab' => $key]) }}"
                                class="tab-link {{ $tab === $key ? 'active' : '' }}">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <table class="application-table">
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>

                @foreach ($applications as $app)
                    <tr>
                        <td>{{ $app->approval ? '承認済み' : '承認待ち' }}</td>
                        <td>{{ $app->work->staff->name }}</td>
                        <td>{{ $app->work->work_date->format('Y/m/d') }}</td>
                        <td>{{ $app->reason }}</td>
                        <td>{{ $app->created_at->format('Y/m/d') }}</td>
                        <td>
                            @if (auth()->user()->isAdmin())
                                <a class="application-detail" href="{{ route('admin.application.show', $app->id) }}">詳細</a>
                            @else
                                <a class="application-detail" href="{{ route('staff.application.show', $app->id) }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
