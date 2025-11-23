<form action="{{ route('attendance.store', $work->id) }}" method="post">
    @csrf
    @include('works.partials._works_fields', [
        'work' => $work,
        'breaks' => $breaks,
        'isReadonly' => $isReadonly
    ])
    <button type="submit" class="submit-btn">修正</button>
</form>
