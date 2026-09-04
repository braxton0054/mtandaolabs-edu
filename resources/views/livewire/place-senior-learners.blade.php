<div class="card">
    <div class="card-header">
        <h4 class="card-title">Place learner into Senior School</h4>
    </div>
    <div class="card-body">
        <x-display-validation-errors/>
        <div class="md:grid grid-cols-3 gap-2">
            <p class="font-bold col-span-3">Junior School source</p>
            <x-select id="junior-class" name="juniorClass" label="Junior class" wire:model.live="juniorClass">
                @foreach ($juniorClasses as $class)
                    <option value="{{$class['id']}}">{{$class['name']}}</option>
                @endforeach
            </x-select>
            <p class="font-bold col-span-3">Senior School destination</p>
            <x-select id="senior-class" name="seniorClass" label="Senior class" wire:model.live="seniorClass">
                @foreach ($seniorClasses as $class)
                    <option value="{{$class['id']}}">{{$class['name']}}</option>
                @endforeach
            </x-select>
            <x-select id="pathway" name="pathway_id" label="Pathway">
                @foreach ($pathways as $pathway)
                    <option value="{{$pathway['id']}}">{{$pathway['name']}}</option>
                @endforeach
            </x-select>
        </div>
        <x-loading-spinner />
        <div wire:loading.remove.delay>
            <form action="{{route('students.place-senior')}}" method="post" class="my-3">
                <input type="hidden" name="senior_class_id" value="{{$seniorClass}}"/>
                <div class="md:grid grid-cols-3 gap-2">
                    <x-select id="student" name="student_id" label="Learner">
                        @isset($students)
                            @foreach ($students as $student)
                                <option value="{{$student['id']}}">{{$student['name']}}</option>
                            @endforeach
                        @endisset
                    </x-select>
                    <x-select id="senior-section" name="senior_section_id" label="Senior section">
                        @isset($seniorSections)
                            @foreach ($seniorSections as $section)
                                <option value="{{$section['id']}}">{{$section['name']}}</option>
                            @endforeach
                        @endisset
                    </x-select>
                    <x-input id="kjsea-score" type="number" step="0.01" min="0" max="100" name="kjsea_score" label="KJSEA score (optional)" placeholder="e.g. 72.5"/>
                </div>
                <p class="font-bold mt-4">Electives (choose exactly 3, at least 2 from the pathway)</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 my-2">
                    @isset($electives)
                        @foreach ($electives as $elective)
                            <label class="inline-flex items-center gap-2 border p-2">
                                <input type="checkbox" name="electives[]" value="{{$elective['id']}}"/>
                                {{$elective['name']}} ({{$elective['pathway']['name'] ?? 'no pathway'}})
                            </label>
                        @endforeach
                    @endisset
                </div>
                @csrf
                <x-button label="Place Learner" theme="primary" icon="fas fa-key" type="submit" class="w-full md:w-3/12"/>
            </form>
        </div>
    </div>
</div>
