<form action="{{ route('p4.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div>
        <label for="file">Choose Excel File:</label>
        <input type="file" name="file" id="file" required>
    </div>
    <button type="submit">Import Package 4 contract</button>
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




<table border="1">
    <thead>
        <tr>
            <th>Column Name</th>
            <th>Required</th>
            <th>Type</th>
            <th>Example Value</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>date</td>
            <td>Yes</td>
            <td>Date (YYYY-MM-DD)</td>
            <td>2025-01-04</td>
            <td>The date when the package contract starts.</td>
        </tr>
        <tr>
            <td>customer</td>
            <td>Yes</td>
            <td>String</td>
            <td>HOMECARE</td>
            <td>The name of the customer (must exist in the customers database).</td>
        </tr>
        <tr>
            <td>maid</td>
            <td>Yes</td>
            <td>String</td>
            <td>TEST MAID</td>
            <td>The name of the maid (must exist in the maids database).</td>
        </tr>
        <tr>
            <td>created_by</td>
            <td>No</td>
            <td>String</td>
            <td>admin</td>
            <td>The user who created the record.</td>
        </tr>
        <tr>
            <td>created_at</td>
            <td>No</td>
            <td>Date (YYYY-MM-DD)</td>
            <td>2025-01-04</td>
            <td>The date when the entry was created.</td>
        </tr>
    </tbody>
</table>
