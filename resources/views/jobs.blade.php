<h1>Available Jobs</h1>
@foreach($jobs as $job)
    <div>
        <h3>{{ $job->title }}</h3>
        <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $job->description), 200) }}</p>
        <a href="/job/{{ $job->id }}">View Details</a>
    </div>
@endforeach
