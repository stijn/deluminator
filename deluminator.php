<?php
/**
 * Plugin Name:       Deluminator
 * Plugin URI:        https://github.com/stijn/deluminator
 * Description:       A light/dark/auto switch. Dark mode sucks the light into the switch; light mode lets it back out. Choose the dark palette under Settings › Deluminator.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Stijn Vogels
 * Author URI:        https://aardling.com
 * License:           MIT
 * Text Domain:       deluminator
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

const DELUMINATOR_VERSION = '1.1.0';
const DELUMINATOR_FILE    = __FILE__;

/** Browser chrome colour (mobile address bar) in light mode. Dark mode uses the palette background. */
const DELUMINATOR_THEME_COLOR_LIGHT = '#ffffff';

require_once __DIR__ . '/includes/palettes.php';
if ( is_admin() ) {
	require_once __DIR__ . '/includes/admin.php';
}
if ( is_readable( __DIR__ . '/includes/updater.php' ) ) {
	require_once __DIR__ . '/includes/updater.php';
}

add_action( 'init', function () {
	load_plugin_textdomain( 'deluminator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

/** 'respect': use the theme's own dark mode when it has one. 'ignore': always use the Deluminator palette. */
function deluminator_theme_dark_setting() {
	return 'ignore' === get_option( 'deluminator_theme_dark', 'respect' ) ? 'ignore' : 'respect';
}

/**
 * Head, before any stylesheet: pick the mode (the visitor's earlier choice, or
 * their device setting) and set it on <html> before anything paints.
 *
 * Also defines window.deluminator, which finds a theme's own dark-mode styles
 * (@media (prefers-color-scheme: dark)) and makes them follow the switch.
 *
 * noptimize keeps Autoptimize from moving it; data-cfasync keeps Cloudflare
 * Rocket Loader from deferring it.
 */
function deluminator_head_early() {
	$palette = deluminator_current_palette();
	$colors  = deluminator_palettes()[ $palette ]['colors'];
	$config  = array(
		'respectTheme' => 'respect' === deluminator_theme_dark_setting(),
		'light'        => DELUMINATOR_THEME_COLOR_LIGHT,
		'dark'         => $colors['bg'],
	);
	?>
<!--noptimize--><meta name="theme-color" id="deluminator-theme-color" content="<?php echo esc_attr( DELUMINATOR_THEME_COLOR_LIGHT ); ?>">
<style id="deluminator-palette">:root{<?php echo deluminator_palette_css_vars( $palette ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitised hex colours. ?>}</style>
<script data-cfasync="false">
(function () {
	var root = document.documentElement, C = <?php echo wp_json_encode( $config ); ?>, mode = null;
	try {
		mode = localStorage.getItem('deluminator');
		if (mode === null) { // Saved by version 0.8.
			var old = localStorage.getItem('deluminator-dark');
			if (old !== null) { mode = old === 'true' ? 'dark' : 'light'; localStorage.setItem('deluminator', mode); localStorage.removeItem('deluminator-dark'); }
		}
	} catch (e) {}
	if (mode !== 'light' && mode !== 'dark') mode = 'auto';

	var D = window.deluminator = { mode: mode, respectTheme: C.respectTheme, themeDark: false, sheets: [], rules: [], nodes: [], colors: C };
	var DARK_Q = /prefers-color-scheme\s*:\s*dark/i, ANY_Q = /prefers-color-scheme/i, PAGE = /(^|,)\s*(html|body|:root)\b/i;

	D.systemDark = function () { return !!(window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches); };
	D.isDark = function (m) { return m === 'dark' || (m === 'auto' && D.systemDark()); };

	// Does this block of rules restyle the page itself (background, text colour or colour variables)?
	function paintsPage(rules) {
		for (var i = 0; i < rules.length; i++) {
			var r = rules[i];
			if (r.selectorText && PAGE.test(r.selectorText) && r.style) {
				if (r.style.backgroundColor || r.style.background || r.style.color) return true;
				for (var j = 0; j < r.style.length; j++) if (r.style[j].indexOf('--') === 0) return true;
			}
		}
		return false;
	}
	function walk(rules) {
		for (var i = 0; i < rules.length; i++) {
			var r = rules[i];
			if (r.media && r.cssRules) {
				var t = r.media.mediaText;
				if (ANY_Q.test(t)) {
					D.rules.push({ rule: r, text: t });
					if (DARK_Q.test(t) && paintsPage(r.cssRules)) D.themeDark = true;
				}
			}
			if (r.cssRules) walk(r.cssRules);
		}
	}
	// Look through same-origin stylesheets for the theme's own colour-scheme rules.
	D.scan = function () {
		for (var i = 0; i < document.styleSheets.length; i++) {
			var s = document.styleSheets[i], rules;
			if (D.sheets.indexOf(s) > -1) continue;
			try { rules = s.cssRules; } catch (e) { continue; } // Other domains, or not loaded yet.
			if (!rules) continue;
			D.sheets.push(s);
			var n = s.ownerNode;
			if (n && n.media && ANY_Q.test(n.media)) {
				D.nodes.push({ el: n, text: n.media });
				if (DARK_Q.test(n.media)) D.themeDark = true;
			}
			walk(rules);
		}
	};
	// Make those rules follow the switch instead of the device: 'auto' restores them.
	D.force = function (m) {
		function map(t) {
			if (m === 'auto') return t;
			return t.replace(/\(\s*prefers-color-scheme\s*:\s*(dark|light)\s*\)/gi, function (_, s) {
				return s.toLowerCase() === m ? '(min-width: 0px)' : '(color: 48842621)';
			});
		}
		for (var i = 0; i < D.rules.length; i++) { try { D.rules[i].rule.media.mediaText = map(D.rules[i].text); } catch (e) {} }
		for (var j = 0; j < D.nodes.length; j++) D.nodes[j].el.media = map(D.nodes[j].text);
	};
	// Theme has its own dark mode (and we respect it): drive that. Otherwise: our palette, theme kept on its light styles.
	D.apply = function () {
		var usePalette = !(D.respectTheme && D.themeDark);
		root.classList.toggle('deluminator-palette', usePalette);
		if (D.rules.length || D.nodes.length) D.force(usePalette ? 'light' : D.mode);
	};
	D.setMode = function (m) {
		var dark = D.isDark(m);
		D.mode = m;
		root.classList.toggle('deluminator-dark', dark);
		root.setAttribute('data-deluminator', m);
		root.style.colorScheme = dark ? 'dark' : 'light';
		D.apply();
		var meta = document.getElementById('deluminator-theme-color');
		if (meta) meta.setAttribute('content', dark ? C.dark : C.light);
		return dark;
	};
	D.setMode(mode);
})();
</script><!--/noptimize-->
	<?php
}
add_action( 'wp_head', 'deluminator_head_early', 1 );

/**
 * Head, after the stylesheets: by now they have loaded, so the theme's own
 * dark-mode rules can be found and switched before the first paint.
 */
function deluminator_head_late() {
	?>
<!--noptimize--><script data-cfasync="false">window.deluminator && (deluminator.scan(), deluminator.apply());</script><!--/noptimize-->
	<?php
}
add_action( 'wp_head', 'deluminator_head_late', 9999 );

function deluminator_enqueue_assets() {
	$url = plugin_dir_url( __FILE__ ) . 'assets/';
	wp_enqueue_style( 'deluminator', $url . 'deluminator.css', array(), DELUMINATOR_VERSION );
	wp_enqueue_script( 'deluminator', $url . 'deluminator.js', array(), DELUMINATOR_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_localize_script( 'deluminator', 'deluminatorSettings', array(
		'labels' => array(
			'state_auto'  => __( 'Colour scheme: automatic, as your device is set.', 'deluminator' ),
			'state_light' => __( 'Colour scheme: light.', 'deluminator' ),
			'state_dark'  => __( 'Colour scheme: dark.', 'deluminator' ),
			'to_auto'     => __( 'Switch to automatic.', 'deluminator' ),
			'to_light'    => __( 'Switch to light.', 'deluminator' ),
			'to_dark'     => __( 'Switch to dark.', 'deluminator' ),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'deluminator_enqueue_assets' );

function deluminator_svg( $name ) {
	$path = __DIR__ . '/assets/' . $name . '.svg';
	return is_readable( $path ) ? file_get_contents( $path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

/**
 * The switch. The three icons ship as <template>s so the script can re-insert
 * them on every change, which replays their drawing animation.
 */
function deluminator_render_switch() {
	?>
<button type="button" id="deluminator" class="deluminator" aria-label="<?php esc_attr_e( 'Colour scheme', 'deluminator' ); ?>">
	<span class="deluminator__icon" aria-hidden="true"></span>
</button>
<template id="deluminator-auto"><?php echo deluminator_svg( 'auto' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled file. ?></template>
<template id="deluminator-light"><?php echo deluminator_svg( 'sun' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled file. ?></template>
<template id="deluminator-dark"><?php echo deluminator_svg( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled file. ?></template>
	<?php
}
add_action( 'wp_footer', 'deluminator_render_switch' );
