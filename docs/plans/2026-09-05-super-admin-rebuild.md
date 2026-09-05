# Super-admin rebuild — plan (2026-09-05)

Approved by Braxton: "yes rebuild". Three parts.

## Part 1 — Lock super-admin menu (platform-only until a school is picked)
- **File:** `app/Livewire/Layouts/Menu.php` (`mount()`)
- After building `$this->menu`, if `auth()->user()->isPlatformAdmin()` AND `!school_context()->has()` → filter to platform-only items:
  - Dashboard, User Profile, `Multi Schools Management` header + Schools item, View Logs
  - Everything else (Administration, Academics groups) hidden
- School admins / anyone with a school set: unchanged.
- **Tests first:** `tests/Feature/PlatformAdminMenuTest.php`
  - platform admin, no school → menu has Dashboard/Schools/View Logs, NOT Fees/Parents/Teachers/Terms/Administration
  - platform admin with school set → full menu
  - school admin with school → full menu (no regression)

## Part 2 — School enter/exit flow
- Exists: `set-school` picker (dashboard + schools.index), `schools.setSchool` POST, `RequireActiveSchool` redirects to `schools.index`.
- Add: per-school **Enter** affordance + current-school banner with **Exit to platform**.
- New: `POST dashboard/schools/exit` → `schools.exit` → `SchoolController@exitSchool` → `SchoolContext::forget()` → redirect `schools.index`.
- Check `resources/views/pages/school/index.blade.php` first; add Enter buttons there if missing.
- **Tests:** exit clears `active_school_id` session; set still works; platform admin w/o school hitting `/dashboard` lands on schools.index.

## Part 3 — Semester → Terms UI rename (labels only)
- Sweep user-facing strings only: blades, `Menu.php` text, lang files, validation messages, notifications, report headings.
- `Semester→Term`, `semester→term`, `SEMESTER→TERM` in display text.
- DO NOT touch: class names, variables, route names/URIs, permission strings, DB tables/columns, FK names, config keys, test method names (unless asserting UI text).
- Verify: grep sweep + FULL test suite green.

## Finish
- `pint --dirty`, full `artisan test --compact`, commit, push (CI/CD auto-deploys), verify login 200.
