<?php
/**
 * Plugin Name:       Deluminator
 * Plugin URI:        https://github.com/stijn/deluminator
 * Description:       A light/dark switch for aardling.com. Dark mode sucks the light into the switch; light mode lets it back out.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Stijn Vogels
 * Author URI:        https://aardling.com
 * License:           MIT
 * Text Domain:       deluminator
 */

defined( 'ABSPATH' ) || exit;

const DELUMINATOR_VERSION = '1.0.0';
const DELUMINATOR_FILE    = __FILE__;

/** Colours the browser chrome (address bar on mobile) takes on in each mode. */
const DELUMINATOR_THEME_COLOR_LIGHT = '#ffffff';
const DELUMINATOR_THEME_COLOR_DARK  = '#141414';

if ( is_readable( __DIR__ . '/includes/updater.php' ) ) {
	require_once __DIR__ . '/includes/updater.php';
}

/**
 * Before anything paints: pick the mode from the visitor's earlier choice,
 * or from their system setting if they never chose. Prevents a white flash
 * on every page load in dark mode.
 *
 * Wrapped in noptimize so Autoptimize leaves it inline and in the head,
 * data-cfasync so Cloudflare Rocket Loader does not defer it.
 */
function deluminator_head() {
	$light = DELUMINATOR_THEME_COLOR_LIGHT;
	$dark  = DELUMINATOR_THEME_COLOR_DARK;
	?>
<!--noptimize--><meta name="theme-color" id="deluminator-theme-color" content="<?php echo esc_attr( $light ); ?>">
<script data-cfasync="false">
(function () {
	var d = document.documentElement, s = null;
	try {
		s = localStorage.getItem('deluminator');
		if (s === null) { // Settings from version 0.8.
			var old = localStorage.getItem('deluminator-dark');
			if (old !== null) { s = old === 'true' ? 'dark' : 'light'; localStorage.setItem('deluminator', s); localStorage.removeItem('deluminator-dark'); }
		}
	} catch (e) {}
	var dark = s === 'dark' || (s !== 'light' && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
	if (dark) d.classList.add('deluminator-dark');
	d.style.colorScheme = dark ? 'dark' : 'light';
	var m = document.getElementById('deluminator-theme-color');
	if (m) m.setAttribute('content', dark ? <?php echo wp_json_encode( $dark ); ?> : <?php echo wp_json_encode( $light ); ?>);
})();
</script><!--/noptimize-->
	<?php
}
add_action( 'wp_head', 'deluminator_head', 1 );

function deluminator_enqueue_assets() {
	$url = plugin_dir_url( __FILE__ ) . 'assets/';
	wp_enqueue_style( 'deluminator', $url . 'deluminator.css', array(), DELUMINATOR_VERSION );
	wp_enqueue_script( 'deluminator', $url . 'deluminator.js', array(), DELUMINATOR_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_localize_script( 'deluminator', 'deluminatorSettings', array(
		'themeColorLight' => DELUMINATOR_THEME_COLOR_LIGHT,
		'themeColorDark'  => DELUMINATOR_THEME_COLOR_DARK,
		'labelToDark'     => __( 'Switch to dark mode', 'deluminator' ),
		'labelToLight'    => __( 'Switch to light mode', 'deluminator' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'deluminator_enqueue_assets' );

function deluminator_svg( $name ) {
	$path = __DIR__ . '/assets/' . $name . '.svg';
	return is_readable( $path ) ? file_get_contents( $path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

/**
 * The switch itself. Both icons ship as <template>s so the script can re-insert
 * them on every switch, which replays their drawing animation.
 */
function deluminator_render_switch() {
	?>
<button type="button" id="deluminator" class="deluminator" aria-pressed="false" aria-label="<?php esc_attr_e( 'Switch to dark mode', 'deluminator' ); ?>" title="<?php esc_attr_e( 'Switch to dark mode', 'deluminator' ); ?>">
	<span class="deluminator__icon" aria-hidden="true"></span>
</button>
<template id="deluminator-sun"><?php echo deluminator_svg( 'sun' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled file. ?></template>
<template id="deluminator-moon"><?php echo deluminator_svg( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled file. ?></template>
	<?php
}
add_action( 'wp_footer', 'deluminator_render_switch' );
