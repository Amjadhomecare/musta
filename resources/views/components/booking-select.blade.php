<div class="col-md-4 mb-3">
    <div class="form-group">
        <label for="{{ $id ?? 'edit_book' }}" class="form-label">Booking and Hold</label>
        <select id="{{ $id ?? 'edit_book' }}" name="{{ $name ?? 'edit_book' }}" class="form-control shadow-sm p-2 bg-white rounded focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            <option value="">Remove Booking</option>
            <option value="Hold">Hold</option>
            <option value="Sick">Sick</option>
            <option value="Vacation">Vacation</option>

        </select>
    </div>
</div>
