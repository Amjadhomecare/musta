
<form action="{{ route('p1.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div>
        <label for="file">Choose Excel File:</label>
        <input type="file" name="file" id="file" required>
    </div>
    <button type="submit">Import Package 1 contract</button>
</form>

@if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

@if($errors->any())
    <ul style="color: red;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

