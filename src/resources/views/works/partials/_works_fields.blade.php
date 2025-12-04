<div class="edit-table">
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
            <input class="input__time" type="time" name="clock_in" value="{{ $work->clock_in?->format('H:i') }}" >
            〜
            <input class="input__time" type="time" name="clock_out" value="{{ $work->clock_out?->format('H:i') }}" >
            @error("clock_in")
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
    </div>
    @php $next = count($breaks); @endphp
    <div id="breakContainer" data-next-index="{{ count($breaks) + 1 }}">
    @foreach ($breaks as $i => $break)
    <div class="row">
        <div class="label">休憩{{ $i + 1 }}</div>
        <div class="value">
            <input class="input__time" type="time" name="break_start[{{ $i }}]" value="{{ optional($break->break_start)->format('H:i')}}" >
            〜
            <input class="input__time" type="time" name="break_end[{{ $i }}]" value="{{ optional($break->break_end)->format('H:i') }}" >
            @error("break_start.$i")
            <p class="error-message">{{ $message }}</p>
            @enderror
            @error("break_end.$i")
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
    </div>
    @endforeach

        <div class="row">
            <div class="label">休憩{{ $next + 1 }}</div>
            <div class="value">
                <input class="input__time" type="time" name="break_start[{{ $next }}]" value="">
                〜
                <input class="input__time" type="time" name="break_end[{{ $next }}]" value="">
                <button type="button" class="add-break-btn">＋</button>
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
        <div class="label">備考</div>
        <div class="value">
            <textarea name="reason" class="reason">@if(auth()->user()->isAdmin()){{ $work->application->reason ?? '' }}@else{{ old('reason') }}@endif</textarea>
            @error('reason')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const container = document.getElementById('breakContainer');

    // 次の index（既存行の数をベースに決める）
    let index = Number(container.dataset.nextIndex);

    function addBreakRow() {
        const row = document.createElement('div');
        row.classList.add('row');
        row.innerHTML = `
            <div class="label">休憩${index + 1}</div>
            <div class="value">
                <input class="input__time" type="time" name="break_start[${index}]" value="">
                〜
                <input class="input__time" type="time" name="break_end[${index}]" value="">
                <button type="button" class="add-break-btn">＋</button>
            </div>
        `;
        container.appendChild(row);

        const rows = Array.from(container.querySelectorAll('.row')).filter(r =>
            r.querySelector('input[name^="break_start"]')
        );
        const buttons = container.querySelectorAll('.add-break-btn');
        buttons.forEach(b => b.style.display = 'none');
        const lastRow = rows[rows.length - 1];
        const lastBtn = lastRow.querySelector('.add-break-btn');
        if (lastBtn) lastBtn.style.display = 'inline-block';

        index++;
    }

    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-break-btn')) {
            addBreakRow();
        }
    });
});
</script>
