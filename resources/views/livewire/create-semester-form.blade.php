<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create term in session {{current_school()->academicYear->name}}</h3>
    </div>
    <div class="card-body">
        <form action="{{route('semesters.store')}}" method="POST" class="md:w-1/2">
            <x-display-validation-errors/>
            <x-input id="name" name="name" label="Term Name" placeholder="Enter term name"/>
            <div class="md:grid grid-cols-2 gap-2">
                <x-input id="start-date" type="date" name="start_date" label="Opening date" value="{{old('start_date')}}"/>
                <x-input id="stop-date" type="date" name="stop_date" label="Closing date" value="{{old('stop_date')}}"/>
                <x-input id="midterm-start" type="date" name="midterm_start" label="Half-term starts" value="{{old('midterm_start')}}"/>
                <x-input id="midterm-stop" type="date" name="midterm_stop" label="Half-term ends" value="{{old('midterm_stop')}}"/>
            </div>
            @csrf
            <div class='col-12 my-2'>
                <x-button label="Create" icon="fas fa-key" type="submit" class="w-full md:w-1/2"/>
            </div>
        </form>
    </div>
</div>