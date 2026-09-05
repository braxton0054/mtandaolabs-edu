<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit {{$semester->name}} in academic year {{current_school()->academicYear->name}}</h3>
    </div>
    <div class="card-body">
        <form action="{{route('semesters.update', $semester->id)}}" method="POST" class="md:w-6/12">
            <x-display-validation-errors/>
            <x-input id="name" name="name" label="Term Name" placeholder="Enter term name" value="{{$semester->name}}"/>
            <div class="md:grid grid-cols-2 gap-2">
                <x-input id="start-date" type="date" name="start_date" label="Opening date" value="{{old('start_date', $semester->start_date)}}"/>
                <x-input id="stop-date" type="date" name="stop_date" label="Closing date" value="{{old('stop_date', $semester->stop_date)}}"/>
                <x-input id="midterm-start" type="date" name="midterm_start" label="Half-term starts" value="{{old('midterm_start', $semester->midterm_start)}}"/>
                <x-input id="midterm-stop" type="date" name="midterm_stop" label="Half-term ends" value="{{old('midterm_stop', $semester->midterm_stop)}}"/>
            </div>
            @csrf
            @method('PUT')
            <x-button label="Edit" theme="primary" icon="fas fa-key" type="submit" class="w-full md:w-1/2"/>
        </form>
    </div>
</div>