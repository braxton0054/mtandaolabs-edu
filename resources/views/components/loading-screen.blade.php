{{-- mtandaolabsEdu loading screen + navigation progress bar --}}
<div id="mtandao-loader" class="mtandao-loader" aria-hidden="true">
    <div class="mtandao-loader-inner">
        <div class="mtandao-loader-logo">
            <img src="{{ asset(config('app.logo')) }}" alt="{{ config('app.name') }}">
        </div>
        <div class="mtandao-loader-spinner"></div>
        <p class="mtandao-loader-text">{{ config('app.name') }}</p>
    </div>
</div>
<div id="mtandao-navbar" class="mtandao-navbar" aria-hidden="true"></div>

<style>
    .mtandao-loader {
        position: fixed; inset: 0; z-index: 99999;
        display: flex; align-items: center; justify-content: center;
        background: #090c12;
        transition: opacity .4s ease, visibility .4s ease;
    }
    .mtandao-loader.done { opacity: 0; visibility: hidden; pointer-events: none; }
    .mtandao-loader-inner { display: flex; flex-direction: column; align-items: center; gap: 20px; }
    .mtandao-loader-logo img {
        width: 88px; height: 88px; border-radius: 22px; object-fit: cover;
        animation: mtandao-pulse 1.6s ease-in-out infinite;
        box-shadow: 0 0 44px rgba(124, 92, 255, 0.35);
    }
    .mtandao-loader-spinner {
        width: 34px; height: 34px;
        border: 3px solid rgba(227, 179, 65, 0.18);
        border-top-color: #e3b341;
        border-radius: 50%;
        animation: mtandao-spin .8s linear infinite;
    }
    .mtandao-loader-text {
        font-family: ui-sans-serif, system-ui, sans-serif;
        font-weight: 700; letter-spacing: .2em; text-transform: uppercase;
        font-size: 13px; color: #e8edf2;
    }
    .mtandao-navbar {
        position: fixed; top: 0; left: 0; height: 3px; width: 0;
        background: linear-gradient(90deg, #7c5cff, #e3b341);
        z-index: 99998; opacity: 0;
        transition: opacity .25s ease;
        pointer-events: none;
    }
    .mtandao-navbar.active { opacity: 1; animation: mtandao-nav 1s ease-in-out infinite alternate; }
    @keyframes mtandao-spin { to { transform: rotate(360deg); } }
    @keyframes mtandao-pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.06); } }
    @keyframes mtandao-nav { from { width: 20%; } to { width: 80%; } }
</style>

<script>
    (function () {
        var loader = document.getElementById('mtandao-loader');
        var bar = document.getElementById('mtandao-navbar');

        function hideLoader() {
            if (!loader || loader.classList.contains('done')) return;
            loader.classList.add('done');
            setTimeout(function () { if (loader && loader.parentNode) loader.parentNode.removeChild(loader); }, 500);
        }
        function hideBar() {
            if (bar) bar.classList.remove('active');
        }

        // Hide splash once the page has fully loaded (with a minimum splash time for polish)
        var minTime = setTimeout(hideLoader, 1200);
        function onLoad() {
            clearTimeout(minTime);
            setTimeout(hideLoader, 250);
            hideBar();
        }
        if (document.readyState === 'complete') onLoad();
        else window.addEventListener('load', onLoad);
        // Safety net: never leave the splash up
        setTimeout(hideLoader, 6000);

        // Show the top progress bar when navigating to another page
        document.addEventListener('click', function (e) {
            var a = e.target && e.target.closest ? e.target.closest('a') : null;
            if (!a) return;
            var href = a.getAttribute('href') || '';
            if (href.startsWith('#') || href.startsWith('http') || href.startsWith('//') || a.target === '_blank' || a.hasAttribute('download')) return;
            if (bar) bar.classList.add('active');
        });
    })();
</script>
