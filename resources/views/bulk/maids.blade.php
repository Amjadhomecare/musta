<form action="{{ route('maids.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div>
        <label for="file">Choose Excel File:</label>
        <input type="file" name="file" id="file" required>
    </div>
    <button type="submit">Import Maids</button>
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
            <td>Jane Doe</td>
            <td>The maid's full name (must be unique in the database).</td>
        </tr>
        <tr>
            <td>salary</td>
            <td>Yes</td>
            <td>Numeric</td>
            <td>1500</td>
            <td>The maid's monthly salary.</td>
        </tr>
        <tr>
            <td>maid_status</td>
            <td>Yes</td>
            <td>String (Enum)</td>
            <td>pending</td>
            <td>Status of the maid (Options: pending, approved, hired).</td>
        </tr>
        <tr>
            <td>nationality</td>
            <td>Yes</td>
            <td>String (Enum)</td>
            <td>Philippines</td>
            <td>Nationality of the maid (Options: Indonesia, Ethiopia, Philippines, Myanmar, etc.).</td>
        </tr>
        <tr>
            <td>maid_type</td>
            <td>Yes</td>
            <td>String (Enum)</td>
            <td>HC</td>
            <td>Type of maid (Options: p1, HC, Direct hire).</td>
        </tr>
        <tr>
            <td>payment</td>
            <td>Yes</td>
            <td>String (Enum)</td>
            <td>cash</td>
            <td>Payment method (Options: cash, bank).</td>
        </tr>
        <tr>
            <td>age</td>
            <td>Yes</td>
            <td>Integer (Min: 18)</td>
            <td>25</td>
            <td>The maid's age (must be at least 18).</td>
        </tr>
        <tr>
            <td>agency</td>
            <td>Yes</td>
            <td>String</td>
            <td>Global Maids Agency</td>
            <td>Name of the agency handling the maid. (Must be define in as ledger)</td>
        </tr>
        <tr>
            <td>note</td>
            <td>No</td>
            <td>String</td>
            <td>Experienced maid</td>
            <td>Additional details about the maid.</td>
        </tr>
        <tr>
            <td>created_by</td>
            <td>No</td>
            <td>String</td>
            <td>admin</td>
            <td>User who created the entry.</td>
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
