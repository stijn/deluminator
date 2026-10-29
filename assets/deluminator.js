/*!
 * Deluminator — light/dark/auto switch.
 * The circular reveal follows SveltePress's ToggleDark (MIT), which is what
 * writewithharper.com used: the View Transitions API snapshots the page before
 * and after, and a clip-path circle grows or shrinks between them from the switch.
 * The clip animation is declared in CSS with fill both, so it holds from the
 * first frame to the last and the page never flashes at either end.
 */
(function () {
	'use strict';

	var root   = document.documentElement;
	var D      = window.deluminator;
	var button = document.getElementById('deluminator');
	var icon   = button && button.querySelector('.deluminator__icon');
	if (!D || !button || !icon) return;

	var labels   = (window.deluminatorSettings && window.deluminatorSettings.labels) || {};
	var icons    = { auto: 'deluminator-auto', light: 'deluminator-light', dark: 'deluminator-dark' };
	var KEY      = 'deluminator';
	var systemQ  = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
	var motionQ  = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
	var current  = null;

	function store(mode) {
		try {
			if (mode === 'auto') localStorage.removeItem(KEY);
			else localStorage.setItem(KEY, mode);
		} catch (e) {}
	}

	// From Auto, the first click always changes what you see; the third click returns to Auto.
	function next(mode) {
		var sys = D.systemDark();
		if (mode === 'auto') return sys ? 'light' : 'dark';
		if (mode === 'light') return sys ? 'dark' : 'auto';
		return sys ? 'auto' : 'light';
	}

	function render(mode) {
		var tpl = document.getElementById(icons[mode]);
		if (tpl) icon.replaceChildren(tpl.content.cloneNode(true)); // A fresh copy replays the drawing animation.
		var label = [labels['state_' + mode], labels['to_' + next(mode)]].filter(Boolean).join(' ');
		if (label) {
			button.setAttribute('aria-label', label);
			button.setAttribute('title', label);
		}
	}

	// Mobile address-bar colour: whatever the page background actually is now.
	function syncThemeColor() {
		var meta = document.getElementById('deluminator-theme-color');
		if (!meta || !document.body) return;
		var bg = getComputedStyle(document.body).backgroundColor;
		if (!bg || bg === 'transparent' || bg === 'rgba(0, 0, 0, 0)') bg = getComputedStyle(root).backgroundColor;
		if (bg && bg !== 'rgba(0, 0, 0, 0)') meta.setAttribute('content', bg);
	}

	function update(mode) {
		D.setMode(mode);
		render(mode);
		syncThemeColor();
	}

	function origin(event) {
		// Keyboard activation reports 0,0: start from the middle of the switch instead.
		if (event && (event.clientX || event.clientY)) return { x: event.clientX, y: event.clientY };
		var r = button.getBoundingClientRect();
		return { x: r.left + r.width / 2, y: r.top + r.height / 2 };
	}

	function clearSwitching() {
		root.classList.remove('deluminator-switching', 'deluminator-to-dark', 'deluminator-to-light');
	}

	function change(mode, event) {
		var wasDark = root.classList.contains('deluminator-dark');
		var dark    = D.isDark(mode);
		store(mode);

		// Same look (e.g. Dark → Auto on a dark device), no transitions, or less motion asked for: just swap.
		if (dark === wasDark || !event || !document.startViewTransition || (motionQ && motionQ.matches)) {
			update(mode);
			return;
		}

		if (current) {
			try { current.skipTransition(); } catch (e) {}
		}

		var o = origin(event);
		var radius = Math.ceil(Math.hypot(Math.max(o.x, window.innerWidth - o.x), Math.max(o.y, window.innerHeight - o.y)));
		root.style.setProperty('--dl-x', o.x + 'px');
		root.style.setProperty('--dl-y', o.y + 'px');
		root.style.setProperty('--dl-r', radius + 'px');
		clearSwitching();
		root.classList.add('deluminator-switching', dark ? 'deluminator-to-dark' : 'deluminator-to-light');

		var t = current = document.startViewTransition(function () { update(mode); });
		t.finished.finally(function () {
			if (current === t) {
				clearSwitching();
				current = null;
			}
		});
	}

	button.addEventListener('click', function (event) {
		change(next(D.mode), event);
	});

	// In Auto, follow the device when it switches (sunset, Focus mode, Control Centre).
	if (systemQ) {
		var follow = function () { if (D.mode === 'auto') update('auto'); else render(D.mode); };
		if (systemQ.addEventListener) systemQ.addEventListener('change', follow);
		else if (systemQ.addListener) systemQ.addListener(follow);
	}

	// Keep tabs in step.
	window.addEventListener('storage', function (e) {
		if (e.key !== KEY) return;
		update(e.newValue === 'light' || e.newValue === 'dark' ? e.newValue : 'auto');
	});

	// Stylesheets that arrive late (deferred or in the footer) may hold the theme's own dark mode.
	function rescan() {
		D.scan();
		D.apply();
		syncThemeColor();
	}
	window.addEventListener('load', rescan);
	rescan();

	render(D.mode);
	button.classList.add('is-ready');
})();
