<form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div>
        <label for="file">Choose Excel File:</label>
        <input type="file" name="file" id="file" required>
    </div>
    <button type="submit">Import Customers</button>
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
            <td>name</td>
            <td>Yes</td>
            <td>String (Unique)</td>
            <td>John Doe</td>
            <td>The full name of the customer (must be unique in the database).</td>
        </tr>
        <tr>
            <td>phone</td>
            <td>Yes</td>
            <td>String (Unique)</td>
            <td>1234567890</td>
            <td>The customer's phone number (must be unique in the database).</td>
        </tr>
        <tr>
            <td>note</td>
            <td>No</td>
            <td>String</td>
            <td>Regular customer</td>
            <td>Any additional information about the customer.</td>
        </tr>
    </tbody>
</table>
