@if (auth()->user()->isPlatformAdmin() || auth()->user()->schoolMemberships()->active()->count() > 1)
<div class="flex flex-wrap items-center gap-2">
    <p class="text-gray-600 dark:text-gray-200 text-xs md:text-base my-2">
        @if (current_school() !== null)
            You are currently on {{current_school()->name}} - {{current_school()->address}}
        @else
            Please set a school
        @endif
    </p>
    @if (auth()->user()->isPlatformAdmin() && current_school() !== null)
        <form action="{{route('schools.exit')}}" method="POST" class="inline">
            @csrf
            <button type="submit" class="text-xs md:text-sm font-medium text-red-600 dark:text-red-400 hover:underline">
                Exit to platform
            </button>
        </form>
    @endif
</div>
@endif
