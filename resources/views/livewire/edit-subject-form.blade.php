<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit subject {{$subject->name}}</h3>
    </div>
    <div class="card-body">
        <form action="{{route('subjects.update', $subject->id)}}" method="POST" class="md:w-6/12">  
        <x-display-validation-errors/>
            <x-input id="name" name="name" label="Subject Name" placeholder="Enter subject name" value="{{$subject->name}}"/>
            <x-input id="short-name" name="short_name" label="Subject Short Name" placeholder="Enter subject short name" value="{{$subject->short_name}}"/>
            <x-select id="pathway-select" name="pathway_id" label="Pathway (Senior electives only)">
                <option value="">No pathway</option>
                @foreach (\App\Models\Pathway::query()->orderBy('name')->get() as $pathway)
                    <option value="{{$pathway->id}}" @selected(old('pathway_id', $subject->pathway_id) == $pathway->id)>{{$pathway->name}}</option>
                @endforeach
            </x-select>
            <div class="my-2">
                <input type="hidden" name="is_compulsory" value="0"/>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_compulsory" value="1" @checked(old('is_compulsory', $subject->is_compulsory))/> Compulsory subject
                </label>
            </div>
            <div class="my-2">
                <input type="hidden" name="is_examinable" value="0"/>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_examinable" value="1" @checked(old('is_examinable', $subject->is_examinable))/> Examinable subject
                </label>
            </div>
            <x-select id="select" name="teachers[]" multiple label="Select Teachers" placeholder="Select teachers.....">
                @foreach ($teachers as $teacher)
                    <option value="{{$teacher->id}}" @selected(in_array($teacher->id, $assignedTeachersId))>{{$teacher->name}}</option>
                @endforeach
            </x-select>
            @csrf
            @method('PUT')
            <x-button label="Edit" icon="fas fa-key" type="submit" class="w-full md:w-1/2"/>
        </form>
    </div>
</div>
