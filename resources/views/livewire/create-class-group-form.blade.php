<div class="card">
    <div class="card-header">
        <h2 class="card-title">Create Class Group</h2>
    </div>
    <div class="card-body">
        <form action="{{route('class-groups.store')}}" method="POST" >
            <x-display-validation-errors />
            <x-input name="name" id="name" type="text" placeholder="Enter name of class group" label="Class group Name *"  class="md:w-6/12"/>
            <x-select id="level" name="level" label="CBC Level" placeholder="Select a level..." class="md:w-6/12">
                @foreach (\App\Enums\CbcLevel::cases() as $level)
                    <option value="{{$level->value}}" @selected(old('level') == $level->value)>{{$level->label()}}</option>
                @endforeach
            </x-select>
            @csrf
            <div class="w-full flex ">
                <x-button theme="primary" icon="fas fa-key" type="submit" class="w-full md:w-3/12">
                    Create
                </x-button>
            </div>
        </form>
    </div>
</div>
