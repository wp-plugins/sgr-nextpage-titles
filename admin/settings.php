<?php
/**
 * Store settings related to the SGR Nextpage Titles plugin
 *
 * @since 0.93
 */
class Nextpage_Titles_Settings {

	/**
	 * All plugin features supported
	 *
	 * @since 0.93
	 * @var array
	 */
	public static $features = array( 'nextpage_titles' => true );

	/**
	 * Add hooks
	 *
	 * @since 0.93
	 */
	public static function init() {
		add_action( 'admin_menu', array( 'Nextpage_Titles_Settings', 'settings_menu_item' ) );
		add_action( 'admin_enqueue_scripts', array( 'Nextpage_Titles_Settings', 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 0.93
	 *
	 * @uses wp_enqueue_style()
	 * @return void
	 */
	public static function enqueue_scripts() {
		wp_enqueue_style( 'multipage-admin', plugins_url( 'static/css/admin/multipage-admin' . ( ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min' ) . '.css', dirname( __FILE__ ) ), array(), '1.5' );
	}

	/**
	 * Add sGR Nextpage Titles settings to the WordPress administration menu.
	 *
	 * @since 0.93
	 *
	 * @global Nextpage_Titles_Loader
	 * @global $submenu array submenu created for the menu slugs
	 * @return void
	 */
	public static function settings_menu_item() {
		global $nextpage_titles_loader;
		
		// main settings page
		if ( ! class_exists( 'Nextpage_Titles_Main_Settings' ) )
			require_once( dirname( __FILE__ ) . '/settings-main.php' );
		
		$menu_hook = Nextpage_Titles_Main_Settings::menu_item();
		if ( ! $menu_hook )
			return;
	}
	
	/**
	 * Standardize the form flow.
	 *
	 * @since 0.93
	 *
	 * @uses settings_fields()
	 * @uses do_settings_sections()
	 * @param string $page_slug constructs custom actions. passed to Settings API functions
	 * @param string $page_title placed in a <h2> at the top of the page
	 * @return void
	 */
	public static function settings_page_template( $page_slug, $page_title ) {
		echo '<div class="wrap">';

		/**
		 * Echo content before the page header.
		 *
		 * @since 0.93
		 */
		do_action( 'nextpage_titles_settings_before_header_' . $page_slug );
		echo '<header><h2>' . esc_html( $page_title ) . '</h2></header>';
		/**
		 * Echo content after the page header.
		 *
		 * @since 1.1
		 */
		do_action( 'nextpage_titles_settings_after_header_' . $page_slug );

		// handle general messages such as settings updated up top
		// place individual settings errors alongside their fields
		//settings_errors( 'general' ); /* Commented because this displays two times settings saved */

		echo '<div id="multipage-settings">';
		echo '<form method="post" action="options.php">';

		settings_fields( $page_slug );
		do_settings_sections( $page_slug );

		submit_button();
		echo '</form>';
		echo '</div><!-- #multipage-settings-->';
		echo '<div id="multipage-sidebar">';
		echo '</div><!-- #multipage-sidebar-->';
		echo '</div>';

		/**
		 * Echo content at the bottom of the page.
		 *
		 * @since 1.1
		 */
		do_action( 'nextpage_titles_settings_footer_' . $page_slug );
	}
}
?>