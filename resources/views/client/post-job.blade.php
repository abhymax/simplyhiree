<!-- resources/views/client/post-job.blade.php -->

<form action="/client/post-job" method="POST">
    @csrf

    <label for="title">Job Title</label>
    <input type="text" name="title" id="title" required>

    <label for="description">Description</label>
    <textarea name="description" id="description" required></textarea>

    <label for="category_id">Category</label>
    <select name="category_id" id="category_id" required>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>

    <button type="submit">Post Job</button>
</form>
