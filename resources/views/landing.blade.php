@extends('layouts.guest')

@section('title', config('app.name', 'mtandaolabsEdu'))

@section('body')
    <div class="min-h-screen bg-background text-foreground">
        {{-- Nav --}}
        <nav class="sticky top-0 z-50 border-b bg-background/85 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset(config('app.logo')) }}" alt="{{ config('app.name') }} logo" class="h-9 w-9 rounded-xl border object-cover">
                    <span class="text-base font-semibold tracking-tight">Mtandao<span class="text-primary">Labs</span><span class="ml-1 text-sm text-muted-foreground">/ Edu</span></span>
                </a>
                <div class="hidden items-center gap-6 text-sm text-muted-foreground md:flex">
                    <a href="#features" class="hover:text-foreground">Features</a>
                    <a href="{{ route('login') }}" class="hover:text-foreground">Login</a>
                </div>
                <div class="flex items-center gap-2">
                    <button id="themeToggle" class="inline-flex h-9 w-9 items-center justify-center rounded-full border text-base transition hover:border-ring" aria-label="Toggle theme">
                        <span class="dark:hidden">🌙</span><span class="hidden dark:inline">☀️</span>
                    </button>
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90">Get started</a>
                    <button id="menuToggle" class="inline-flex h-9 w-9 items-center justify-center rounded-full border md:hidden" aria-label="Menu">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
            <div id="mobileMenu" class="hidden border-t px-4 py-3 md:hidden">
                <div class="flex flex-col gap-3 text-sm text-muted-foreground">
                    <a href="#features" class="hover:text-foreground">Features</a>
                    <a href="{{ route('login') }}" class="hover:text-foreground">Login</a>
                </div>
            </div>
        </nav>

        {{-- Hero --}}
        <header class="relative overflow-hidden">
            <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 pb-16 pt-14 sm:px-6 md:grid-cols-2 md:pt-20">
                <div>
                    <p class="mb-4 text-xs font-semibold uppercase tracking-[0.2em] text-primary">School admin, simplified</p>
                    <h1 class="text-4xl font-bold leading-tight tracking-tight sm:text-5xl">Run your school<br><span class="text-primary">from one place.</span></h1>
                    <p class="mt-5 max-w-md text-base text-muted-foreground">mtandaolabsEdu helps Kenyan schools manage classes, students, teachers, exams and fees — without spreadsheets and paper trails.</p>
                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition hover:opacity-90">Sign in →</a>
                        <a href="#features" class="inline-flex items-center rounded-full border px-5 py-2.5 text-sm font-medium transition hover:border-ring">See features</a>
                    </div>
                    <p class="mt-5 text-xs text-muted-foreground">
                        <span class="mr-2 inline-block h-2 w-2 rounded-full bg-primary"></span>Multi-school ready · Built for the CBC curriculum
                    </p>
                </div>

                {{-- Dashboard mockup --}}
                <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                    <div class="flex items-center gap-1.5 border-b px-4 py-3">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-yellow-400"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                        <span class="ml-2 font-mono text-[11px] text-muted-foreground">dashboard.mtandaolabsEdu</span>
                    </div>
                    <div class="grid grid-cols-1 md:min-h-[240px] md:grid-cols-[110px_1fr]">
                        <div class="hidden border-r bg-muted/40 p-3 md:block">
                            <div class="mb-2 flex items-center gap-2 rounded-md bg-primary/10 px-2 py-1.5 text-[11px] text-primary">
                                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                Dashboard
                            </div>
                            <div class="mb-2 flex items-center gap-2 px-2 py-1.5 text-[11px] text-muted-foreground">
                                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>
                                Students
                            </div>
                            <div class="mb-2 flex items-center gap-2 px-2 py-1.5 text-[11px] text-muted-foreground">
                                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                Teachers
                            </div>
                            <div class="flex items-center gap-2 px-2 py-1.5 text-[11px] text-muted-foreground">
                                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                                Exams
                            </div>
                        </div>
                        <div class="space-y-3 p-4">
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-lg border p-2.5"><div class="text-lg font-bold">1,240</div><div class="text-[10px] uppercase tracking-wide text-muted-foreground">Students</div></div>
                                <div class="rounded-lg border p-2.5"><div class="text-lg font-bold">64</div><div class="text-[10px] uppercase tracking-wide text-muted-foreground">Teachers</div></div>
                                <div class="rounded-lg border p-2.5"><div class="text-lg font-bold">98%</div><div class="text-[10px] uppercase tracking-wide text-muted-foreground">Fee paid</div></div>
                            </div>
                            <div class="flex h-14 items-end gap-1.5 rounded-lg border p-2">
                                @foreach ([45, 60, 52, 75, 68, 90, 82] as $i => $h)
                                    <div class="flex-1 rounded-sm bg-primary/{{ ($i % 3 === 0) ? 80 : (($i % 3 === 1) ? 40 : 60) }}" style="height: {{ $h }}%"></div>
                                @endforeach
                            </div>
                            <div class="overflow-hidden rounded-lg border">
                                <div class="grid grid-cols-[2fr_1fr_1fr] border-b bg-muted/40 px-3 py-1.5 text-[10px] uppercase tracking-wide text-muted-foreground"><span>Student</span><span>Class</span><span>Average</span></div>
                                <div class="grid grid-cols-[2fr_1fr_1fr] border-b px-3 py-1.5 text-[11px]"><span>Anne Wanjiku</span><span>Grade 6</span><span class="text-primary">A</span></div>
                                <div class="grid grid-cols-[2fr_1fr_1fr] px-3 py-1.5 text-[11px]"><span>Kevin Otieno</span><span>Grade 4</span><span class="text-primary">B+</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Marquee --}}
            <div class="overflow-hidden border-y py-3">
                <div class="flex w-max animate-[scroll_30s_linear_infinite] gap-8 whitespace-nowrap">
                    @php
                        $items = ['STUDENTS', 'CLASSES', 'TEACHERS', 'EXAMS', 'MARKS', 'FEES', 'REPORTS', 'TIMETABLES', 'MULTI-SCHOOL'];
                    @endphp
                    @foreach (array_merge($items, $items) as $i => $item)
                        <span class="text-xs font-medium tracking-[0.2em] text-muted-foreground">
                            {{ $item }}@if ($i % 2 === 0) <span class="ml-8 text-primary">◆</span>@endif
                        </span>
                    @endforeach
                </div>
            </div>
        </header>

        {{-- Features --}}
        <section id="features" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="mx-auto mb-12 max-w-xl text-center">
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-primary">Why mtandaolabsEdu</p>
                <h2 class="text-3xl font-bold tracking-tight">Everything your school needs.</h2>
                <p class="mt-3 text-muted-foreground">One system for the whole school — built with the Kenyan CBC curriculum in mind.</p>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $features = [
                        ['icon' => '<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/><path d="M22 10v6"/>', 'title' => 'Students & admissions', 'desc' => 'Manage student records, classes and streams. Admit, promote and graduate with one click.'],
                        ['icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', 'title' => 'Teachers & staff', 'desc' => 'Teacher profiles, class assignments and subject allocation — clear and simple.'],
                        ['icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/>', 'title' => 'Exams & marks', 'desc' => 'Mid-term and end-term exams, CBC strand-based assessment, and automatic averages.'],
                        ['icon' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M6 15h4"/>', 'title' => 'Fees & payments', 'desc' => 'Fee structures per class and term, balance tracking, receipts — M-Pesa ready.'],
                        ['icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/>', 'title' => 'Timetables', 'desc' => 'Build class timetables with time slots, weekdays and teacher assignments.'],
                        ['icon' => '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>', 'title' => 'Multi-school', 'desc' => 'One platform, many schools. Perfect for education groups, branches and franchises.'],
                    ];
                @endphp
                @foreach ($features as $f)
                    <div class="rounded-lg border bg-card p-6 transition hover:border-ring">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $f['icon'] !!}</svg>
                        </div>
                        <h3 class="text-base font-semibold">{{ $f['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-muted-foreground">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- CTA --}}
        <section class="mx-auto max-w-6xl px-4 pb-20 sm:px-6">
            <div class="rounded-xl border bg-primary/5 p-10 text-center">
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Ready to modernise your school?</h2>
                <p class="mx-auto mt-3 max-w-md text-muted-foreground">Talk to Mtandao Labs about getting your school on mtandaolabsEdu.</p>
                <div class="mt-6 flex justify-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground transition hover:opacity-90">Sign in</a>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="border-t py-8">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 text-xs text-muted-foreground sm:px-6">
                <p class="font-mono">mtandaolabsEdu — a product of <a href="https://mtandaolabs.com" class="text-primary">Mtandao Labs</a></p>
                <p class="font-mono">© {{ date('Y') }} Mtandao Labs · Nairobi, Kenya</p>
            </div>
        </footer>
    </div>

    <style>
        @keyframes scroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    </style>
    <script>
        (function () {
            // Theme toggle (class-based, matches app)
            var toggle = document.getElementById('themeToggle');
            function applyTheme(t) {
                document.documentElement.classList.toggle('dark', t === 'dark');
                try { localStorage.setItem('theme', t); } catch (e) {}
            }
            try {
                var saved = localStorage.getItem('theme');
                if (saved) applyTheme(saved);
            } catch (e) {}
            if (toggle) toggle.addEventListener('click', function () {
                applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
            });
            // Mobile menu
            var menuToggle = document.getElementById('menuToggle');
            var mobileMenu = document.getElementById('mobileMenu');
            if (menuToggle && mobileMenu) menuToggle.addEventListener('click', function () {
                mobileMenu.classList.toggle('hidden');
            });
        })();
    </script>
@endsection
