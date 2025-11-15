@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">

    @include('staff.work.button.not_working')

</div>
@endsection
