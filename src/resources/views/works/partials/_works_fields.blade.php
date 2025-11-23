<div class="row">
    <div class="label">名前</div>
    <div class="value">{{ $work->staff->name }}</div>
</div>
<div class="row">
    <div class="label">日付</div>
    <div class="value">{{ $work->work_date->format('Y年 n月 j日') }}</div>
</div>
<div class="row">
    <div class="label">出勤・退勤</div>
    <div class="value">
        <input type="time" name="clock_in" value="{{ $work->clock_in?->format('H:i') }}" >
        〜
        <input type="time" name="clock_out" value="{{ $work->clock_out?->format('H:i') }}" >
    </div>
</div>
@foreach ($breaks as $i => $break)
    <div class="row">
        <div class="label">休憩{{ $i + 1 }}</div>
        <div class="value">
            <input type="time" name="break_start[]" value="{{ optional($break->break_start)->format('H:i')}}" >
            〜
            <input type="time" name="break_end[]" value="{{ optional($break->break_end)->format('H:i') }}" >
        </div>
    </div>
@endforeach
<div class="row">
    <div class="label">備考</div>
    <div class="value">
        <textarea name="reason" class="reason" >
        @if(auth()->user()->isAdmin())
        {{ $work->application->reason ?? '' }}
        @else
        {{ old('reason') }}
        @endif
        </textarea>
    </div>
</div>
