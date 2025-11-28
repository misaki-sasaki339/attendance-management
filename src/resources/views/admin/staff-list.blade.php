@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
<div class="container">
    <h2>スタッフ一覧</h2>

    <table class="staff-list">
        <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th>月次勤怠</th>
        </tr>

        @foreach($staffs as $staff)
        <tr>
            <td>{{ $staff->name }}</td>
            <td>{{ $staff->email }}</td>
            <td>
                <a href="{{ route('admin.staffMonthly', $staff->id ) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection
