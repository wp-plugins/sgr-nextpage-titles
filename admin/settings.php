<?php
/**
 * Store settings related to the SGR Nextpage Titles plugin
 *
 * @since 0.8
 */
class Nextpage_Titles_Settings {

	/**
	 * All plugin features supported
	 *
	 * @since 0.8
	 * @var array
	 */
	public static $features = array( 'nextpage_titles' => true );

	/**
	 * Add hooks
	 *
	 * @since 0.8
	 */
	public static function init() {
		add_action( 'admin_menu', array( 'Nextpage_Titles_Settings', 'settings_submenu_page' ) );
	}

	function settings_submenu_page() {
		add_submenu_page( 'themes.php', 'NextPage Titles Options', 'NextPage Titles', 'manage_options', 'sgr_nextpage_titles', 'my_plugin_options');
	}

	public static function my_plugin_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		echo '<div class="wrap">';
		echo '<p>Here is where the form would go if I actually had options.</p>';
		echo '</div>';
	}
}
?>