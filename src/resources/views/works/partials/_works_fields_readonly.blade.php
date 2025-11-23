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
        <time>{{ $work->clock_in?->format('H:i') }}</time>
        〜
        <time>{{ $work->clock_out?->format('H:i') }}</time>
    </div>
</div>
@forelse ($breaks as $i => $break)
    <div class="row">
        <div class="label">休憩{{ $i + 1 }}</div>
        <div class="value">
            <time>{{ optional($break->break_start)->format('H:i')}}</time>
            〜
            <time>{{ optional($break->break_end)->format('H:i') }}</time>
        </div>
    </div>
@empty
    <div class="row">
        <div class="label">休憩</div>
        <div class="value"></div>
    </div>
@endforelse
<div class="row">
    <div class="label">備考</div>
    <div class="value">
        <p>{{ $work->reason }}</p>
    </div>
</div>
