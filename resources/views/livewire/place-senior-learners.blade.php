<div class="card">
    <div class="card-header">
        <h4 class="card-title">Place learner into Senior School</h4>
    </div>
    <div class="card-body">
        <x-display-validation-errors/>
        <div class="space-y-4">
            <div>
                <p class="font-bold mb-2">Junior School source</p>
                <x-select id="junior-class" name="juniorClass" label="Junior class" wire:model.live="juniorClass">
                    @foreach ($juniorClasses as $class)
                        <option value="{{$class['id']}}">{{$class['name']}}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <p class="font-bold mb-2">Senior School destination</p>
                <div class="md:grid md:grid-cols-2 gap-3">
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
            </div>
        </div>
        <x-loading-spinner />
        <div wire:loading.remove.delay>
            <form action="{{route('students.place-senior')}}" method="post" class="my-4 space-y-4">
                <input type="hidden" name="senior_class_id" value="{{$seniorClass}}"/>
                <div class="md:grid md:grid-cols-3 gap-3">
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
                <div>
                    <p class="font-bold mb-2">Electives</p>
                    <p class="text-sm text-secondary mb-3">Choose exactly 3. At least 2 must come from the chosen pathway.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        @isset($electives)
                            @foreach ($electives as $elective)
                                <label class="inline-flex items-center gap-2 border rounded p-2 hover:bg-muted/50 cursor-pointer">
                                    <input type="checkbox" name="electives[]" value="{{$elective['id']}}" class="rounded"/>
                                    <span class="flex-1">{{$elective['name']}}</span>
                                    <span class="text-xs text-secondary">{{$elective['pathway']['name'] ?? 'no pathway'}}</span>
                                </label>
                            @endforeach
                        @endisset
                    </div>
                </div>
                @csrf
                <div class="flex">
                    <x-button label="Place Learner" theme="primary" icon="fas fa-key" type="submit" class="w-full md:w-3/12"/>
                </div>
            </form>
        </div>
    </div>
</div>
