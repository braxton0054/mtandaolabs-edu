<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create grade</h3>
    </div>
    <div class="card-body">
        <form action="{{route('grade-systems.store')}}" method="post" class="md:w-1/2">
            <x-display-validation-errors/>
            <p class="text-secondary">
                {{__('All fields marked * are required')}}
            </p>
            <x-input id="name" name="name" label="Name *" placeholder="Grade name eg A1" />
            <x-input id="remark" name="remark" label="Remark" placeholder="Grade remark eg Excellent" />
            <x-input id="grade-from" type="number" name="grade_from" label="From *" placeholder="Grade from eg 10" />
            <x-input id="grade-till" type="number" name="grade_till" label="Till *" placeholder="Grade till eg 20" />
            <x-select id="class-group" name="class_group_id" fgroup-class="col-md-6 mx-1" label="Class Group *">
                @foreach ($classGroups as $classGroup)
                    <option value="{{$classGroup->id}}" @selected(old('class_group_id') == $classGroup->id)>{{$classGroup->name}}</option>
                @endforeach
            </x-select>
            <x-select id="competency-level" name="competency_level_id" fgroup-class="col-md-6 mx-1" label="Competency Level">
                <option value="">No level</option>
                @foreach (\App\Models\CompetencyLevel::query()->orderBy('sort_order')->get() as $competency)
                    <option value="{{$competency->id}}" @selected(old('competency_level_id') == $competency->id)>{{$competency->code}} - {{$competency->name}}</option>
                @endforeach
            </x-select>
            <div class='col-12 my-2'>
                <x-button label="Create" theme="primary" icon="fas fa-key" type="submit" class="w-full md:w-1/2"/>
            </div>
            @csrf
        </form>
    </div>
</div>