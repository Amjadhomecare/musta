
@extends('keen')
@section('content')

  
    <div class="container">
        <h2>Select Month and Year for Salary Calculation</h2>
        <form action="{{route('getMaidsSalariesPayRollsForCat4MaidsCntl')}}" method="get">
           

           
            <div class="form-group">
                <label for="year">Year:</label>
                <select  name="year" id="year" class="form-control">
                    @for($i = now()->year; $i >= now()->year - 2; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>

          
            <div class="form-group">
                <label for="month">Month:</label>
                <select  name="month" id="month" class="form-control">
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                    @endforeach
                </select>
            </div>

            <br><br><br>

            
            <button type="submit" class="btn btn-primary">Calculate Maids Category 4 Salaries</button>
        </form>
    </div>
@endsection
