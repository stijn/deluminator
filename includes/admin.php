<?php
/**
 * Settings › Deluminator: choose the dark palette, and whether a theme's own dark mode wins.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_options_page(
		__( 'Deluminator', 'deluminator' ),
		__( 'Deluminator', 'deluminator' ),
		'manage_options',
		'deluminator',
		'deluminator_render_settings_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'deluminator', 'deluminator_palette', array(
		'type'              => 'string',
		'default'           => deluminator_default_palette(),
		'sanitize_callback' => function ( $value ) {
			$value = sanitize_key( (string) $value );
			return array_key_exists( $value, deluminator_palettes() ) ? $value : deluminator_default_palette();
		},
	) );
	register_setting( 'deluminator', 'deluminator_theme_dark', array(
		'type'              => 'string',
		'default'           => 'respect',
		'sanitize_callback' => function ( $value ) {
			return 'ignore' === $value ? 'ignore' : 'respect';
		},
	) );
} );

add_filter( 'plugin_action_links_' . plugin_basename( DELUMINATOR_FILE ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=deluminator' ) ) . '">' . esc_html__( 'Settings', 'deluminator' ) . '</a>' );
	return $links;
} );

/**
 * Rough check of the active theme's stylesheets for dark-mode rules, to tell the
 * admin what to expect. The front end decides for itself in the browser.
 */
function deluminator_theme_has_dark_mode() {
	$dirs  = array_unique( array( get_stylesheet_directory(), get_template_directory() ) );
	$found = false;
	foreach ( $dirs as $dir ) {
		$files = array_merge(
			glob( $dir . '/*.css' ) ?: array(),
			glob( $dir . '/{assets,css,dist,build}/{,*/}*.css', GLOB_BRACE ) ?: array()
		);
		foreach ( array_slice( $files, 0, 40 ) as $file ) {
			if ( filesize( $file ) > 2 * MB_IN_BYTES ) {
				continue;
			}
			$css = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( $css && preg_match( '/prefers-color-scheme\s*:\s*dark/i', $css ) ) {
				$found = true;
				break 2;
			}
		}
	}
	return $found;
}

function deluminator_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$palettes   = deluminator_palettes();
	$current    = deluminator_current_palette();
	$theme_dark = deluminator_theme_dark_setting();
	$theme      = wp_get_theme();
	$has_dark   = deluminator_theme_has_dark_mode();
	?>
	<div class="wrap deluminator-settings">
		<h1><?php esc_html_e( 'Deluminator', 'deluminator' ); ?></h1>
		<p class="deluminator-intro"><?php esc_html_e( 'Visitors switch between automatic, light and dark with the round button in the top-right corner. Automatic follows their device.', 'deluminator' ); ?></p>

		<form action="options.php" method="post">
			<?php settings_fields( 'deluminator' ); ?>

			<h2><?php esc_html_e( 'Dark palette', 'deluminator' ); ?></h2>
			<p><?php esc_html_e( 'The colours used in dark mode when the theme has no dark mode of its own.', 'deluminator' ); ?></p>

			<fieldset class="deluminator-palettes">
				<legend class="screen-reader-text"><?php esc_html_e( 'Dark palette', 'deluminator' ); ?></legend>
				<?php foreach ( $palettes as $key => $palette ) : ?>
					<label class="deluminator-palette-card" style="<?php echo esc_attr( deluminator_palette_css_vars( $key ) ); ?>">
						<input type="radio" name="deluminator_palette" value="<?php echo esc_attr( $key ); ?>" <?php checked( $current, $key ); ?>>
						<span class="deluminator-preview" aria-hidden="true">
							<span class="dl-p-title">Aardling</span>
							<span class="dl-p-text">The quick brown fox <span class="dl-p-link">jumps over</span> the lazy dog.</span>
							<span class="dl-p-muted">28 oktober 2026 · Stijn Vogels</span>
							<span class="dl-p-panel"><span class="dl-p-pill">Lees verder →</span><span class="dl-p-mark">mark</span><span class="dl-p-accent">1</span></span>
						</span>
						<span class="deluminator-palette-name">
							<?php echo esc_html( $palette['label'] ); ?>
							<?php if ( deluminator_default_palette() === $key ) : ?>
								<span class="deluminator-default"><?php esc_html_e( 'default', 'deluminator' ); ?></span>
							<?php endif; ?>
						</span>
						<span class="deluminator-palette-desc"><?php echo esc_html( $palette['description'] ); ?></span>
					</label>
				<?php endforeach; ?>
			</fieldset>

			<h2><?php esc_html_e( 'Theme with its own dark mode', 'deluminator' ); ?></h2>
			<p>
				<?php
				if ( $has_dark ) {
					/* translators: %s: theme name */
					printf( esc_html__( '%s appears to have its own dark mode.', 'deluminator' ), '<strong>' . esc_html( $theme->get( 'Name' ) ) . '</strong>' );
				} else {
					/* translators: %s: theme name */
					printf( esc_html__( '%s doesn’t appear to have its own dark mode, so the palette above is used.', 'deluminator' ), '<strong>' . esc_html( $theme->get( 'Name' ) ) . '</strong>' );
				}
				?>
			</p>
			<fieldset class="deluminator-theme-dark">
				<legend class="screen-reader-text"><?php esc_html_e( 'Theme with its own dark mode', 'deluminator' ); ?></legend>
				<label>
					<input type="radio" name="deluminator_theme_dark" value="respect" <?php checked( $theme_dark, 'respect' ); ?>>
					<?php esc_html_e( 'Use the theme’s dark mode when it has one; the switch then controls it (recommended)', 'deluminator' ); ?>
				</label><br>
				<label>
					<input type="radio" name="deluminator_theme_dark" value="ignore" <?php checked( $theme_dark, 'ignore' ); ?>>
					<?php esc_html_e( 'Always use the Deluminator palette', 'deluminator' ); ?>
				</label>
			</fieldset>

			<?php submit_button(); ?>
		</form>
	</div>
	<style>
		.deluminator-settings .deluminator-intro { font-size: 14px; max-width: 46rem; }
		.deluminator-palettes { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px; max-width: 900px; margin: 12px 0 8px; }
		.deluminator-palette-card { position: relative; display: flex; flex-direction: column; gap: 6px; padding: 10px; border: 2px solid #dcdcde; border-radius: 10px; background: #fff; cursor: pointer; }
		.deluminator-palette-card:hover { border-color: #8c8f94; }
		.deluminator-palette-card:has(input:checked) { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; }
		.deluminator-palette-card input { position: absolute; top: 18px; right: 18px; margin: 0; }
		.deluminator-palette-card input:focus-visible + .deluminator-preview { outline: 2px solid #2271b1; outline-offset: 2px; }
		.deluminator-preview { display: flex; flex-direction: column; gap: 6px; padding: 14px 14px 12px; border-radius: 6px; background: var(--dl-bg); color: var(--dl-text); font: 13px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
		.dl-p-title { color: var(--dl-strong); font: 600 19px/1.2 Georgia, serif; padding-right: 26px; }
		.dl-p-link { color: var(--dl-link); text-decoration: underline; }
		.dl-p-muted { color: var(--dl-muted); font-size: 11px; }
		.dl-p-panel { display: flex; align-items: center; gap: 8px; padding: 8px; border-radius: 6px; background: var(--dl-surface); border: 1px solid var(--dl-line); }
		.dl-p-pill { flex: 1; padding: 3px 8px; border-radius: 20px; background: var(--dl-surface-2); color: var(--dl-link); font-weight: 700; font-size: 11px; text-align: center; }
		.dl-p-mark { padding: 0 4px; background: var(--dl-mark); color: var(--dl-mark-text); font-size: 11px; }
		.dl-p-accent { display: inline-grid; place-items: center; width: 18px; height: 18px; border-radius: 9px; background: var(--dl-accent); color: var(--dl-accent-text); font: 11px monospace; }
		.deluminator-palette-name { font-weight: 600; font-size: 14px; }
		.deluminator-default { margin-left: 4px; padding: 1px 6px; border-radius: 8px; background: #f0f0f1; color: #50575e; font-weight: 400; font-size: 11px; }
		.deluminator-palette-desc { color: #50575e; font-size: 12px; line-height: 1.45; }
	</style>
	<?php
}
