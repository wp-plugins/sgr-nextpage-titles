<?php
/**
 * Remove data written by the Multipage plugin for WordPress after an administrative user clicks "Delete" from the plugin management page in the WordPress administrative interface (wp-admin).
 *
 * @since 1.3
 */

// only execute as part of an uninstall script
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

// site options
$__options = array(
	'multipage_intro_mode',
	'multipage_migration_122'
);

foreach ( $__options as $option_name ) {
	delete_option( $option_name );
}
unset( $__options );
?>