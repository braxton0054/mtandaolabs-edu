<div class="card">
    <div class="card-header">
        <h2 class="card-title">Edit grade {{$grade->name}}</h2>
    </div>
    <div class="card-body">
        <form action="{{route('grade-systems.update' ,$grade->id)}}" method="post" class="md:w-1/2">
            <x-input  id="name" name="name" label="Name" placeholder="Grade name eg A1"  value="{{$grade->name}}"/>
            <x-input  id="remark" name="remark" label="Remark" placeholder="Grade remark eg Excellent"  value="{{$grade->remark}}"/>
            <x-input  id="grade-from" type="number" name="grade_from" label="From" placeholder="Grade from eg 10"  value="{{$grade->grade_from}}"/>
            <x-input  id="grade-till" type="number" name="grade_till" label="Till" placeholder="grade till eg 20"  value="{{$grade->grade_till}}"/>
            <x-select id="class-group"  name="class_group_id" fgroup-class="col-md-6 mx-1" label="Class Group" wire:model.live="classGroup">
                @foreach ($classGroups as $classGroup)
                    <option value="{{$classGroup->id}}">{{$classGroup->name}}</option>
                @endforeach
            </x-select >
            <x-select id="competency-level" name="competency_level_id" fgroup-class="col-md-6 mx-1" label="Competency Level">
                <option value="">No level</option>
                @foreach (\App\Models\CompetencyLevel::query()->orderBy('sort_order')->get() as $competency)
                    <option value="{{$competency->id}}" @selected(old('competency_level_id', $grade->competency_level_id) == $competency->id)>{{$competency->code}} - {{$competency->name}}</option>
                @endforeach
            </x-select>
            <x-button label="Edit" icon="fas fa-pen" type="submit" class="w-full md:w-1/2"/>
            
            @csrf
            @method("PUT")
        </form>
    </div>
</div>