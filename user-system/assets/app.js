/**
 * app.js - small front-end enhancements
 *  1. Dark / light mode toggle (saved in localStorage)
 *  2. Show / hide password buttons
 *  3. Password strength meter
 */
document.addEventListener('DOMContentLoaded', function () {

    // ---- 1. Dark mode toggle ------------------------------------------------
    var html = document.documentElement;
    var themeBtn = document.getElementById('themeToggle');

    function syncIcon() {
        if (!themeBtn) return;
        var dark = html.getAttribute('data-bs-theme') === 'dark';
        themeBtn.innerHTML = dark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-stars-fill"></i>';
    }
    syncIcon();

    if (themeBtn) {
        themeBtn.addEventListener('click', function () {
            var next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
            syncIcon();
        });
    }

    // ---- 2. Show / hide password -------------------------------------------
    document.querySelectorAll('.toggle-pass').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.querySelector(btn.getAttribute('data-target'));
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
        });
    });

    // ---- 3. Password strength meter ----------------------------------------
    var pw = document.getElementById('password');
    var bar = document.getElementById('strengthFill');
    var label = document.getElementById('strengthLabel');

    if (pw && bar && label) {
        pw.addEventListener('input', function () {
            var v = pw.value, score = 0;
            if (v.length >= 8) score++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            if (v.length >= 12) score++;

            var levels = [
                { w: '0%',   c: 'transparent', t: '' },
                { w: '25%',  c: '#dc3545',     t: 'Weak' },
                { w: '50%',  c: '#fd7e14',     t: 'Fair' },
                { w: '75%',  c: '#ffc107',     t: 'Good' },
                { w: '100%', c: '#198754',     t: 'Strong' }
            ];
            var lvl = v.length === 0 ? 0 : Math.min(Math.max(score - 1, 1), 4);
            bar.style.width = levels[lvl].w;
            bar.style.background = levels[lvl].c;
            label.textContent = levels[lvl].t ? 'Strength: ' + levels[lvl].t : '';
        });
    }
});
