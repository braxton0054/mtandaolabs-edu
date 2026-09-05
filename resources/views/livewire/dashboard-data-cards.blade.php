<div class="space-y-6">
    @if (auth()->user()->isPlatformAdmin() || auth()->user()->hasRole(\App\Enums\Role::Admin))
    <april:card>
        <slot:title>Overview</slot:title>
        <slot:description>A quick look at the people and academic structure in your school.</slot:description>
        <slot:content class="space-y-6">
            @can('read school')
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <a href="{{route('schools.index')}}" class="group flex items-center gap-4 rounded-xl border bg-card p-4 transition-colors hover:border-primary/50 md:p-5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><path d="M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-2xl font-bold leading-none md:text-3xl">{{$schools}}</span>
                            <span class="mt-1 block text-xs uppercase tracking-wide text-muted-foreground">Schools</span>
                        </span>
                        <span class="ml-auto text-sm text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-primary">→</span>
                    </a>
                </div>
            @endcan

            @can('manage school settings')
                <div class="flex items-center gap-3 border-t pt-6">
                    <div class="h-2 w-2 rounded-full bg-primary"></div>
                    <h3 class="font-semibold">School data</h3>
                </div>
            @endcan

            @php
                $cards = [
                    ['can' => 'read class group', 'count' => $classGroups, 'label' => 'Class groups', 'url' => route('class-groups.index'), 'icon' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>'],
                    ['can' => 'read class', 'count' => $classes, 'label' => 'Classes', 'url' => route('classes.index'), 'icon' => '<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/>'],
                    ['can' => 'read section', 'count' => $sections, 'label' => 'Sections', 'url' => route('sections.index'), 'icon' => '<path d="M3 21h18"/><path d="M6 21V8M10 21V8M14 21V8M18 21V8"/><path d="M3 8l9-5 9 5H3z"/>'],
                    ['can' => 'read student', 'count' => $students, 'label' => 'Students (active)', 'url' => route('students.index'), 'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
                    ['can' => 'read teacher', 'count' => $teachers, 'label' => 'Teachers', 'url' => route('teachers.index'), 'icon' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>'],
                    ['can' => 'read parent', 'count' => $parents, 'label' => 'Parents', 'url' => route('parents.index'), 'icon' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>'],
                ];
            @endphp
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($cards as $card)
                    @can($card['can'])
                        <a href="{{$card['url']}}" class="group flex items-center gap-4 rounded-xl border bg-card p-4 transition-colors hover:border-primary/50 md:p-5">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $card['icon'] !!}</svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-2xl font-bold leading-none md:text-3xl">{{$card['count']}}</span>
                                <span class="mt-1 block text-xs uppercase tracking-wide text-muted-foreground">{{$card['label']}}</span>
                            </span>
                            <span class="ml-auto text-sm text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-primary">→</span>
                        </a>
                    @endcan
                @endforeach
            </div>
        </slot:content>
    </april:card>
    @endif
</div>
