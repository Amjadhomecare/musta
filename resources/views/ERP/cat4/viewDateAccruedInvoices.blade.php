@extends('keen')
@section('content')

<style>

.custom-form {
    max-width: 600px;
    margin: 20px auto;
    padding: 30px;
    background: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 10px;
    color: #333;
    font-size: 16px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
    transition: border-color 0.3s;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
    outline: none;
}

.submit-btn {
    background-color: #28a745;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s, box-shadow 0.3s;
}

.submit-btn:hover {
    background-color: #218838;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}


</style>

<link rel="stylesheet" href="path_to_your_stylesheet.css">

<form method="get" action="{{route('viewAccruedDateCat4Cntl')}}" class="custom-form">

    <div class="form-group">
        <label for="range-datepicker" class="form-label">Select Date Range For Upcomming installment</label>
        <input type="text" id="range-datepicker" class="form-control" name="date_range" placeholder="YYYY-MM-DD to YYYY-MM-DD">
    </div>


    <button type="submit" class="submit-btn">Submit</button>
</form>

<script>
    $(function() {
        $('#range-datepicker').daterangepicker({
            locale: {
              format: 'YYYY-MM-DD'
            },
            opens: 'right',
            drops: 'down',
            autoApply: true,
        });
    });
</script>



@endsection
