<h1>Available Jobs</h1>
@foreach($jobs as $job)
    <div>
        <h3>{{ $job->title }}</h3>
        <p>{{ $job->description }}</p>
        <a href="/job/{{ $job->id }}">View Details</a>
    </div>
@endforeach
