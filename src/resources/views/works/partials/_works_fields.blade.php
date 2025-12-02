<div class="row">
    <div class="label">名前</div>
    <div class="value">{{ $work->staff->name }}</div>
</div>
<div class="row">
    <div class="label">日付</div>
    <div class="value">{{ $work->work_date->format('Y年n月j日') }}</div>
</div>
<div class="row">
    <div class="label">出勤・退勤</div>
    <div class="value">
        <input type="time" name="clock_in" value="{{ $work->clock_in?->format('H:i') }}" >
        〜
        <input type="time" name="clock_out" value="{{ $work->clock_out?->format('H:i') }}" >
        @error("clock_in")
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>
</div>
@foreach ($breaks as $i => $break)
<div class="row">
    <div class="label">休憩{{ $i + 1 }}</div>
    <div class="value">
        <input type="time" name="break_start[{{ $i }}]" value="{{ optional($break->break_start)->format('H:i')}}" >
        〜
        <input type="time" name="break_end[{{ $i }}]" value="{{ optional($break->break_end)->format('H:i') }}" >
        @error("break_start.$i")
        <p class="error-message">{{ $message }}</p>
        @enderror
        @error("break_end.$i")
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>
</div>
@endforeach

{{-- 空行を追加 --}}
@php $next = count($breaks); @endphp
<div id="breakContainer" data-next-index="{{ $next + 1 }}">
    <div class="row">
        <div class="label">休憩{{ $next + 1 }}</div>
        <div class="value">
            <input type="time" name="break_start[{{ $next }}]" value="">
            〜
            <input type="time" name="break_end[{{ $next }}]" value="">
            @error("break_start.$next")
            <p class="error-message">{{ $message }}</p>
            @enderror
            @error("break_end.$next")
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
<div class="row">
    <button type="button" id="addBreakBtn" class="btn">休憩を追加</button>
</div>
<div class="row">
    <div class="label">備考</div>
    <div class="value">
        <textarea name="reason" class="reason">@if(auth()->user()->isAdmin()){{ $work->application->reason ?? '' }}@else{{ old('reason') }}@endif</textarea>
        @error('reason')
        <p class="error-message">{{ $message }}</p>
        @enderror
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {

    const addBtn = document.getElementById('addBreakBtn');
    const container = document.getElementById('breakContainer');

    // 次の index（既存行の数をベースに決める）
    let index = Number(container.dataset.nextIndex);

    addBtn.addEventListener('click', () => {

        // 新しい休憩行を作成
        const row = document.createElement('div');
        row.classList.add('row');

        row.innerHTML = `
            <div class="label">休憩${index + 1}</div>
            <div class="value">
                <input type="time" name="break_start[${index}]" value="">
                〜
                <input type="time" name="break_end[${index}]" value="">
            </div>
        `;

        container.appendChild(row);
        index++;
    });
});
</script>
