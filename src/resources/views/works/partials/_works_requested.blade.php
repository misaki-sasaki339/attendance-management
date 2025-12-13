<div class="application-table">
    <div class="row">
        <div class="label">名前</div>
        <div class="value value-space">{{ $work->staff->name }}</div>
    </div>
    <div class="row">
        <div class="label">日付</div>
        <div class="value value-space">
            <span>{{ $work->work_date->format('Y年') }}</span>
            <span>{{ $work->work_date->format('n月j日') }}</span>
        </div>
    </div>
    <div class="row">
        <div class="label">出勤・退勤</div>
        <div class="value">
            <time class="input__time">{{ $application->new_clock_in->format('H:i') }}</time>
            〜
            <time class="input__time">{{ $application->new_clock_out->format('H:i') }}</time>
        </div>
    </div>

    @php
        $requestedBreaks = json_decode($application->new_break_times, true) ?? [];
    @endphp

    @forelse ($requestedBreaks as $i => $break)
        <div class="row">
            <div class="label">休憩{{ $i + 1 }}</div>
            <div class="value">
                <time class="input__time">{{ $break['start'] }}</time>
                〜
                <time class="input__time">{{ $break['end'] }}</time>
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
            <p class="reason">{{ $application->reason }}</p>
        </div>
    </div>
</div>
