<div class="container-app py-10">
    <h1 class="mb-8 text-3xl font-extrabold text-slate-900">{{ __('courses.my.title') }}</h1>

    @if ($courses->isEmpty())
        <div class="card grid place-items-center gap-4 p-16 text-center">
            <p class="text-slate-500">{{ __('courses.my.empty') }}</p>
            <a href="{{ route('courses.index') }}" class="btn-primary">{{ __('courses.my.browse') }}</a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
                <x-course-card :course="$course" />
            @endforeach
        </div>
    @endif
</div>
