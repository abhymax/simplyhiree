<h1>Available Jobs</h1>
<ul>
    @foreach($jobs as $job)
        <li>
            <h2>{{ $job->title }}</h2>
            <p>{{ $job->description }}</p>
            <a href="{{ route('jobs.show', $job->id) }}">View Details</a>
        </li>
    @endforeach
</ul>
@foreach ($jobs as $job)
    <div class="job-item">
        <h3>{{ $job->title }}</h3>
        <p>{{ $job->description }}</p>

        <!-- Apply Button -->
        <form action="/apply/{{ $job->id }}" method="POST">
            @csrf
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Apply Now</button>
        </form>
    </div>
@endforeach

