<form action="/register-candidate" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="mobile_number">Mobile Number</label>
    <input type="text" name="mobile_number" required>

    <label for="resume">Upload Resume</label>
    <input type="file" name="resume">

    <button type="submit">Register</button>
</form>
