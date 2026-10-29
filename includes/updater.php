<?php
/**
 * Self-updating from GitHub (stijn/deluminator).
 *
 * - plugin-update-checker (MIT, vendored) looks at the latest GitHub release and
 *   offers its "deluminator.zip" asset as an update. Checks every 12 hours.
 * - Public repo: no token needed. If the repo is ever made private, add a read-only
 *   token in wp-config.php: define( 'DELUMINATOR_GITHUB_TOKEN', '...' );
 * - WordPress auto-updates are switched on for this plugin only.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_readable( dirname( __DIR__ ) . '/vendor/plugin-update-checker/plugin-update-checker.php' ) ) {
	return;
}

require_once dirname( __DIR__ ) . '/vendor/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

const DELUMINATOR_REPO = 'https://github.com/stijn/deluminator/';

function deluminator_update_checker() {
	static $checker = null;
	if ( null === $checker ) {
		$checker = PucFactory::buildUpdateChecker( DELUMINATOR_REPO, DELUMINATOR_FILE, 'deluminator', 12 );
		if ( defined( 'DELUMINATOR_GITHUB_TOKEN' ) && DELUMINATOR_GITHUB_TOKEN ) {
			$checker->setAuthentication( (string) DELUMINATOR_GITHUB_TOKEN );
		}
		$checker->getVcsApi()->enableReleaseAssets( '/deluminator\.zip($|[?&#])/i' );
	}
	return $checker;
}
deluminator_update_checker();

// Auto-update this plugin only.
add_filter( 'auto_update_plugin', function ( $update, $item ) {
	if ( isset( $item->plugin ) && plugin_basename( DELUMINATOR_FILE ) === $item->plugin ) {
		return true;
	}
	return $update;
}, 10, 2 );
