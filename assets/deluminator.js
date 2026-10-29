/*!
 * Deluminator — light/dark switch for aardling.com.
 * The circular reveal follows SveltePress's ToggleDark (MIT), which is what
 * writewithharper.com used: it animates a real snapshot of the page with the
 * View Transitions API rather than a flat coloured overlay.
 */
(function () {
	'use strict';

	var root      = document.documentElement;
	var button    = document.getElementById('deluminator');
	var icon      = button && button.querySelector('.deluminator__icon');
	var sun       = document.getElementById('deluminator-sun');
	var moon      = document.getElementById('deluminator-moon');
	var meta      = document.getElementById('deluminator-theme-color');
	var settings  = window.deluminatorSettings || {};
	var KEY       = 'deluminator';
	var DARK      = 'deluminator-dark';
	var systemDark = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
	var lessMotion = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;

	if (!button || !icon) return;

	function stored() {
		try { return localStorage.getItem(KEY); } catch (e) { return null; }
	}

	function store(value) {
		try { localStorage.setItem(KEY, value); } catch (e) {}
	}

	function isDark() {
		return root.classList.contains(DARK);
	}

	function showIcon(dark) {
		var tpl = dark ? moon : sun;
		if (!tpl) return;
		icon.replaceChildren(tpl.content.cloneNode(true)); // Fresh copy replays the drawing animation.
	}

	function apply(dark) {
		root.classList.toggle(DARK, dark);
		root.style.colorScheme = dark ? 'dark' : 'light';
		if (meta) meta.setAttribute('content', dark ? settings.themeColorDark : settings.themeColorLight);
		var label = dark ? settings.labelToLight : settings.labelToDark;
		if (label) {
			button.setAttribute('aria-label', label);
			button.setAttribute('title', label);
		}
		button.setAttribute('aria-pressed', dark ? 'true' : 'false');
		showIcon(dark);
	}

	function origin(event) {
		// Keyboard activation reports 0,0: start from the middle of the switch instead.
		if (event && (event.clientX || event.clientY)) return { x: event.clientX, y: event.clientY };
		var r = button.getBoundingClientRect();
		return { x: r.left + r.width / 2, y: r.top + r.height / 2 };
	}

	function toggle(event) {
		var dark = !isDark();
		store(dark ? 'dark' : 'light');

		if (!document.startViewTransition || (lessMotion && lessMotion.matches)) {
			apply(dark);
			return;
		}

		var o = origin(event);
		var radius = Math.hypot(
			Math.max(o.x, window.innerWidth - o.x),
			Math.max(o.y, window.innerHeight - o.y)
		);

		root.classList.add('deluminator-switching');
		var transition = document.startViewTransition(function () { apply(dark); });

		transition.ready.then(function () {
			var small = 'circle(0px at ' + o.x + 'px ' + o.y + 'px)';
			var large = 'circle(' + radius + 'px at ' + o.x + 'px ' + o.y + 'px)';
			// To dark: the lit page shrinks into the switch. To light: it grows back out.
			root.animate(
				{ clipPath: dark ? [large, small] : [small, large] },
				{
					duration: 450,
					easing: dark ? 'cubic-bezier(.55,0,.75,.45)' : 'cubic-bezier(.25,.55,.45,1)',
					pseudoElement: dark ? '::view-transition-old(root)' : '::view-transition-new(root)'
				}
			);
		}).catch(function () {});

		transition.finished.finally(function () {
			root.classList.remove('deluminator-switching');
		});
	}

	button.addEventListener('click', toggle);

	// Follow the system setting until the visitor makes a choice of their own.
	if (systemDark) {
		var follow = function (e) { if (stored() === null) apply(e.matches); };
		if (systemDark.addEventListener) systemDark.addEventListener('change', follow);
		else if (systemDark.addListener) systemDark.addListener(follow);
	}

	// Keep tabs in step: switching in one tab switches the others.
	window.addEventListener('storage', function (e) {
		if (e.key === KEY && (e.newValue === 'dark' || e.newValue === 'light')) apply(e.newValue === 'dark');
	});

	apply(isDark()); // The head script already set the mode; sync the switch with it.
	button.classList.add('is-ready');
})();
