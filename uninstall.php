<?php
/**
 * Removes Deluminator's settings when the plugin is deleted.
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
delete_option( 'deluminator_palette' );
delete_option( 'deluminator_theme_dark' );
