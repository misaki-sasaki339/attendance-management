<form action="{{ route('admin.update', ['id' => $work->id]) }}" method="post">
    @csrf
    @method('PUT')
    @include('works.partials._works_fields', [
        'work' => $work,
        'breaks' => $breaks,
        'isReadonly' => $isReadonly
    ])
    <button type="submit" class="submit-btn">修正</button>
</form>
