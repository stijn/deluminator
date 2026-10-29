<?php
/**
 * Dark palettes. Every text colour is checked against every background it can
 * sit on: WCAG Dark meets AAA (7:1), Solarized Dark and Dracula meet AA (4.5:1).
 *
 * Keys map to CSS custom properties: 'link_hover' becomes --dl-link-hover.
 */

defined( 'ABSPATH' ) || exit;

function deluminator_palettes() {
	$palettes = array(
		'solarized' => array(
			'label'       => 'Solarized Dark',
			'description' => __( 'Ethan Schoonover’s low-glare blue-green. Two shades nudged for contrast: the secondary grey and the blue behind white text.', 'deluminator' ),
			'colors'      => array(
				'bg'          => '#002b36',
				'surface'     => '#03303b',
				'surface_2'   => '#073642',
				'line'        => '#657b83',
				'text'        => '#93a1a1',
				'strong'      => '#eee8d5',
				'muted'       => '#8b9b9d',
				'link'        => '#5aaaeb',
				'link_hover'  => '#93c9f2',
				'accent'      => '#1f6fa8',
				'accent_text' => '#fdf6e3',
				'mark'        => '#b58900',
				'mark_text'   => '#002b36',
				'selection'   => '#0e4f61',
			),
		),
		'wcag'      => array(
			'label'       => 'WCAG Dark',
			'description' => __( 'Neutral near-black with the highest contrast: all text meets WCAG AAA (7:1 or more).', 'deluminator' ),
			'colors'      => array(
				'bg'          => '#121212',
				'surface'     => '#1e1e1e',
				'surface_2'   => '#2a2a2a',
				'line'        => '#6b6b6b',
				'text'        => '#e6e6e6',
				'strong'      => '#ffffff',
				'muted'       => '#bdbdbd',
				'link'        => '#a8c7fa',
				'link_hover'  => '#d3e3fd',
				'accent'      => '#a8c7fa',
				'accent_text' => '#121212',
				'mark'        => '#ffe58f',
				'mark_text'   => '#121212',
				'selection'   => '#3a5a8c',
			),
		),
		'dracula'   => array(
			'label'       => 'Dracula',
			'description' => __( 'Zeno Rocha’s purple-and-pink theme, ported to hundreds of apps. The comment grey is lightened for readable secondary text.', 'deluminator' ),
			'colors'      => array(
				'bg'          => '#282a36',
				'surface'     => '#21222c',
				'surface_2'   => '#343746',
				'line'        => '#6272a4',
				'text'        => '#f8f8f2',
				'strong'      => '#ffffff',
				'muted'       => '#b6bdd6',
				'link'        => '#bd93f9',
				'link_hover'  => '#ff79c6',
				'accent'      => '#bd93f9',
				'accent_text' => '#282a36',
				'mark'        => '#f1fa8c',
				'mark_text'   => '#282a36',
				'selection'   => '#44475a',
			),
		),
	);

	/**
	 * Add or change palettes. Each needs 'label', 'description' and the same 'colors' keys.
	 */
	return apply_filters( 'deluminator_palettes', $palettes );
}

function deluminator_default_palette() {
	return 'solarized';
}

/** The chosen palette key, falling back to the default if the saved one no longer exists. */
function deluminator_current_palette() {
	$palettes = deluminator_palettes();
	$key      = get_option( 'deluminator_palette', deluminator_default_palette() );
	return isset( $palettes[ $key ] ) ? $key : deluminator_default_palette();
}

/** CSS custom property declarations for a palette, e.g. "--dl-bg:#002b36;--dl-text:#93a1a1;". */
function deluminator_palette_css_vars( $key ) {
	$palettes = deluminator_palettes();
	if ( ! isset( $palettes[ $key ] ) ) {
		return '';
	}
	$css = '';
	foreach ( $palettes[ $key ]['colors'] as $name => $value ) {
		$value = sanitize_hex_color( $value );
		if ( $value ) {
			$css .= '--dl-' . str_replace( '_', '-', sanitize_key( $name ) ) . ':' . $value . ';';
		}
	}
	return $css;
}
