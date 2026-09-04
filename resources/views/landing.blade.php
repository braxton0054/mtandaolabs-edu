<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <title>mtandaolabsEdu — School Management System</title>
    <meta name="description" content="mtandaolabsEdu is a modern school management system for Kenyan schools — classes, students, teachers, exams, fees and more in one place.">
    <style>
        :root {
            --bg: #090c12;
            --bg-soft: #0d121a;
            --panel: #0f151e;
            --panel-2: #131a25;
            --hairline: rgba(148, 163, 184, 0.14);
            --text: #e8edf2;
            --text-muted: #93a1ad;
            --text-dim: #5c6b78;
            --accent: #7c5cff;
            --accent-soft: rgba(124, 92, 255, 0.14);
            --gold: #e3b341;
            --gold-soft: rgba(227, 179, 65, 0.12);
            --radius: 6px;
            --ease: cubic-bezier(0.16, 1, 0.3, 1);
            --font: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
            --mono: 'JetBrains Mono', ui-monospace, monospace;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: var(--font); background: var(--bg); color: var(--text); line-height: 1.6; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        .container { width: min(1100px, 92%); margin-inline: auto; }
        .mono { font-family: var(--mono); }
        .gold { color: var(--gold); }
        .accent { color: var(--accent); }
        .page-bg { position: fixed; inset: 0; z-index: -1; pointer-events: none; }
        .bg-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(148,163,184,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148,163,184,0.05) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: radial-gradient(ellipse 90% 60% at 50% 0%, #000 40%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 90% 60% at 50% 0%, #000 40%, transparent 100%);
        }
        .bg-glow {
            position: absolute; width: 700px; height: 500px; top: -160px; left: 50%; transform: translateX(-50%);
            background: radial-gradient(closest-side, rgba(124,92,255,0.18), transparent);
        }

        /* Nav */
        .nav { position: sticky; top: 0; z-index: 50; background: rgba(9,12,18,0.82); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border-bottom: 1px solid var(--hairline); }
        .nav-inner { height: 70px; display: flex; align-items: center; justify-content: space-between; }
        .logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.05rem; letter-spacing: -0.01em; }
        .logo img { width: 34px; height: 34px; border-radius: 10px; object-fit: cover; }
        .logo-accent { color: var(--accent); }
        .logo-tag { color: var(--text-dim); font-size: 0.78rem; margin-left: 2px; }
        .nav-links { display: flex; align-items: center; gap: 26px; font-size: 0.82rem; }
        .nav-links a { color: var(--text-muted); transition: color 0.25s var(--ease); }
        .nav-links a:hover { color: var(--text); }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 24px; border-radius: 999px; font-weight: 600; font-size: 0.92rem;
            cursor: pointer; border: 1px solid transparent; transition: transform 0.25s var(--ease), box-shadow 0.25s var(--ease), background 0.25s var(--ease);
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-gold { background: var(--gold); color: #10092b; }
        .btn-gold:hover { box-shadow: 0 10px 30px rgba(227,179,65,0.25); }
        .btn-ghost { border-color: var(--hairline); color: var(--text); background: transparent; }
        .btn-ghost:hover { border-color: var(--accent); color: #fff; }
        .btn-accent { background: var(--accent); color: #fff; }
        .btn-accent:hover { box-shadow: 0 10px 30px rgba(124,92,255,0.3); }

        /* Hero */
        .hero { padding: clamp(64px, 10vw, 130px) 0 50px; position: relative; }
        .hero-grid { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 56px; align-items: center; }
        .eyebrow { font-size: 0.72rem; letter-spacing: 0.22em; text-transform: uppercase; color: var(--gold); margin-bottom: 16px; }
        .hero h1 { font-size: clamp(2.4rem, 5.5vw, 3.9rem); line-height: 1.05; letter-spacing: -0.02em; font-weight: 700; }
        .hero-sub { margin: 22px 0 30px; color: var(--text-muted); font-size: 1.06rem; max-width: 540px; }
        .hero-ctas { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
        .hero-proof { margin-top: 22px; color: var(--text-dim); font-size: 0.78rem; display: flex; align-items: center; gap: 8px; }
        .proof-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); box-shadow: 0 0 0 4px rgba(227,179,65,0.15); }
        .hero-visual { position: relative; }
        .hero-frame {
            position: relative; border-radius: 14px; overflow: hidden; border: 1px solid var(--hairline);
            box-shadow: 0 30px 80px rgba(0,0,0,0.5); background: var(--panel);
        }
        .hero-frame img { width: 100%; aspect-ratio: 16/10; object-fit: cover; }
        .hero-frame .frame-overlay {
            position: absolute; inset: 0; display: flex; align-items: flex-end;
            background: linear-gradient(180deg, transparent 50%, rgba(6,8,12,0.9));
            padding: 22px;
        }
        .frame-chip { font-size: 0.78rem; color: rgba(232,237,242,0.9); letter-spacing: 0.06em; }
        .frame-chip b { color: #fff; font-size: 1rem; }

        /* Feature strip */
        .marquee { margin-top: 60px; overflow: hidden; border-top: 1px solid var(--hairline); border-bottom: 1px solid var(--hairline); padding: 14px 0; white-space: nowrap; }
        .marquee-track { display: inline-flex; align-items: center; gap: 26px; animation: scroll 30s linear infinite; }
        .marquee-track span { font-size: 0.76rem; letter-spacing: 0.18em; color: var(--text-muted); }
        .marquee-track i { color: var(--gold); font-style: normal; font-size: 0.6rem; }
        @keyframes scroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* Sections */
        section { padding: 90px 0 40px; }
        .section-head { text-align: center; margin-bottom: 46px; }
        .section-head h2 { font-size: clamp(1.8rem, 4vw, 2.6rem); line-height: 1.12; letter-spacing: -0.015em; font-weight: 600; }
        .section-head .sub { color: var(--text-muted); max-width: 620px; margin: 12px auto 0; }

        .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .feature {
            padding: 28px 24px; border-radius: var(--radius); border: 1px solid var(--hairline);
            background: linear-gradient(180deg, var(--panel), rgba(15,21,30,0.5));
            transition: transform 0.3s var(--ease), border-color 0.3s var(--ease);
        }
        .feature:hover { transform: translateY(-4px); border-color: rgba(124,92,255,0.4); }
        .f-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 42px; height: 42px; border-radius: 8px; background: var(--accent-soft); color: var(--accent);
            font-size: 1.05rem; margin-bottom: 14px;
        }
        .feature h3 { font-size: 1.1rem; font-weight: 600; margin-bottom: 6px; }
        .feature p { color: var(--text-muted); font-size: 0.9rem; }

        /* CTA band */
        .cta-band { padding: 30px 0 90px; }
        .cta-card {
            border-radius: 16px; padding: 56px 40px; text-align: center;
            background: linear-gradient(135deg, rgba(124,92,255,0.12), rgba(227,179,65,0.08));
            border: 1px solid rgba(124,92,255,0.3);
        }
        .cta-card h2 { font-size: clamp(1.6rem, 3.5vw, 2.4rem); font-weight: 700; margin-bottom: 12px; }
        .cta-card p { color: var(--text-muted); margin-bottom: 28px; }

        .footer { border-top: 1px solid var(--hairline); padding: 30px 0; }
        .footer-inner { display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--text-muted); }
        .footer a { color: var(--gold); }

        @media (max-width: 900px) {
            .hero-grid { grid-template-columns: 1fr; gap: 40px; }
            .feature-grid { grid-template-columns: 1fr 1fr; }
            .nav-links { display: none; }
        }
        @media (max-width: 600px) {
            .feature-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page-bg" aria-hidden="true">
        <div class="bg-grid"></div>
        <div class="bg-glow"></div>
    </div>

    <nav class="nav">
        <div class="container nav-inner">
            <a class="logo" href="{{ url('/') }}">
                <img src="{{ asset(config('app.logo')) }}" alt="mtandaolabsEdu logo">
                <span>Mtandao<span class="logo-accent">Labs</span><span class="logo-tag mono">/ Edu</span></span>
            </a>
            <div class="nav-links mono">
                <a href="#features">Features</a>
                <a href="{{ route('login') }}">Login</a>
            </div>
            <a class="btn btn-gold" href="{{ route('login') }}">Get started</a>
        </div>
    </nav>

    <header class="hero">
        <div class="container hero-grid">
            <div class="hero-copy">
                <p class="eyebrow mono">SCHOOL ADMIN, SIMPLIFIED</p>
                <h1>Run your school<br>from <span class="gold">one place.</span></h1>
                <p class="hero-sub">mtandaolabsEdu helps Kenyan schools manage classes, students, teachers, exams and fees — without spreadsheets and paper trails.</p>
                <div class="hero-ctas">
                    <a class="btn btn-gold" href="{{ route('login') }}">Sign in →</a>
                    <a class="btn btn-ghost mono" href="#features">See features</a>
                </div>
                <div class="hero-proof mono">
                    <span class="proof-dot"></span> Multi-school ready · Built for the CBC curriculum
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-frame">
                    <img src="{{ asset(config('app.logo')) }}" alt="mtandaolabsEdu dashboard preview">
                    <div class="frame-overlay">
                        <div class="frame-chip mono"><b>mtandaolabsEdu</b> — students · marks · fees · reports</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="marquee mono" aria-hidden="true">
            <div class="marquee-track">
                <span>STUDENTS</span><i>◆</i><span>CLASSES</span><i>◆</i><span>TEACHERS</span><i>◆</i><span>EXAMS</span><i>◆</i><span>MARKS</span><i>◆</i><span>FEES</span><i>◆</i><span>REPORTS</span><i>◆</i><span>TIMETABLES</span><i>◆</i><span>MULTI-SCHOOL</span><i>◆</i><span>STUDENTS</span><i>◆</i><span>CLASSES</span><i>◆</i><span>TEACHERS</span><i>◆</i><span>EXAMS</span><i>◆</i><span>MARKS</span><i>◆</i><span>FEES</span><i>◆</i><span>REPORTS</span><i>◆</i><span>TIMETABLES</span><i>◆</i><span>MULTI-SCHOOL</span><i>◆</i>
            </div>
        </div>
    </header>

    <section id="features">
        <div class="container">
            <div class="section-head">
                <p class="eyebrow mono">WHY MTANDAOLABSEDU</p>
                <h2>Everything your school needs.</h2>
                <p class="sub">One system for the whole school — built with the Kenyan CBC curriculum in mind.</p>
            </div>
            <div class="feature-grid">
                <div class="feature">
                    <div class="f-icon">🎓</div>
                    <h3>Students &amp; admissions</h3>
                    <p>Manage student records, classes and streams. Admit, promote and graduate with one click.</p>
                </div>
                <div class="feature">
                    <div class="f-icon">👩‍🏫</div>
                    <h3>Teachers &amp; staff</h3>
                    <p>Teacher profiles, class assignments and subject allocation — clear and simple.</p>
                </div>
                <div class="feature">
                    <div class="f-icon">📝</div>
                    <h3>Exams &amp; marks</h3>
                    <p>Mid-term and end-term exams, CBC strand-based assessment, and automatic averages.</p>
                </div>
                <div class="feature">
                    <div class="f-icon">💰</div>
                    <h3>Fees &amp; payments</h3>
                    <p>Fee structures per class and term, balance tracking, receipts — M-Pesa ready.</p>
                </div>
                <div class="feature">
                    <div class="f-icon">📅</div>
                    <h3>Timetables</h3>
                    <p>Build class timetables with time slots, weekdays and teacher assignments.</p>
                </div>
                <div class="feature">
                    <div class="f-icon">🏫</div>
                    <h3>Multi-school</h3>
                    <p>One platform, many schools. Perfect for education groups, branches and franchises.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="cta-band">
        <div class="container">
            <div class="cta-card">
                <h2>Ready to modernise your school?</h2>
                <p>Talk to Mtandao Labs about getting your school on mtandaolabsEdu.</p>
                <div class="hero-ctas" style="justify-content:center;">
                    <a class="btn btn-gold" href="{{ route('login') }}">Sign in</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container footer-inner">
            <p class="mono">mtandaolabsEdu — a product of <a href="https://mtandaolabs.com">Mtandao Labs</a></p>
            <p class="mono dim">© {{ date('Y') }} Mtandao Labs · Nairobi, Kenya</p>
        </div>
    </footer>
</body>
</html>
