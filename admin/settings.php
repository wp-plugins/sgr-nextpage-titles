<?php
/**
 * Store settings related to the Multipage Plugin
 *
 * @since 0.93
 */
class Multipage_Plugin_Settings {

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
		add_action( 'admin_menu', array( 'Multipage_Plugin_Settings', 'settings_menu_item' ) );
		add_action( 'admin_enqueue_scripts', array( 'Multipage_Plugin_Settings', 'enqueue_scripts' ) );
		
		// Check if current user can edit posts & pages
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
			
			// Check if TinyMCE is enabled
			if ( get_user_option( 'rich_editing' ) == 'true') {
			
				// Add TinyMCE Plugin
				add_filter( 'mce_css', array( 'Multipage_Plugin_Settings', 'multipage_mce_css' ) );
				add_filter( 'mce_buttons', array( 'Multipage_Plugin_Settings', 'multipage_mce_button' ) );
				add_filter( 'mce_external_plugins', array( 'Multipage_Plugin_Settings', 'multipage_mce_external_plugin' ) );
				add_filter( 'mce_external_languages', array( 'Multipage_Plugin_Settings', 'multipage_mce_external_language' ) );
			
			}
			
			// Add HTML Editor button
			add_action( 'admin_print_footer_scripts', array( 'Multipage_Plugin_Settings', 'appthemes_add_quicktags' ) );
		}
	}
	
	/**
	 * Add HTML Text Editor Subpage button
	 *
	 * @since 1.3
	 */
	public static function appthemes_add_quicktags() {
		if ( wp_script_is( 'quicktags' ) ) {
	?>
	<script type="text/javascript">
		QTags.addButton( 'eg_subpage', '<?php _e( 'subpage', 'sgr-npt' ); ?>', prompt_subtitle, '', '', '<?php _e( 'Start a new Subpage', 'sgr-npt' ); ?>', 121 );
		
		function prompt_subtitle(e, c, ed) {
			var subtitle = prompt( '<?php _e( 'Enter the subpage title', 'sgr-npt' ); ?>' ),
				shortcode, t = this;

			if (typeof subtitle != 'undefined' && subtitle.length < 2) return;

			t.tagStart = '[nextpage title="' + subtitle + '"]\n\n';
			t.tagEnd = false;
			
			// now we've defined all the tagStart, tagEnd and openTags we process it all to the active window
			QTags.TagButton.prototype.callback.call(t, e, c, ed);
		};
	</script>
	<?php
			}
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
	 * Add a new TinyMCE css.
	 *
	 * @since 1.3
	 *
	 * @return string
	 */
	public static function multipage_mce_css( $mce_css ) {
		if ( ! empty( $mce_css ) )
			$mce_css .= ',';

		$mce_css .= plugins_url( 'admin/tinymce/css/multipage' . ( ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min' ) . '.css', dirname( __FILE__ ) );
		return $mce_css;
	}

	/**
	 * Add the new subpage TinyMCE button.
	 *
	 * @since 1.3
	 *
	 * @return array $buttons
	 */
	public static function multipage_mce_button( $buttons ) {
		// Insert 'Subpage' button after the 'WP More' button
		$wp_more_key = array_search( 'wp_more', $buttons ) +1;
		$buttons_after = array_splice( $buttons, $wp_more_key);
		
		array_unshift($buttons_after, 'subpage');
		
		$buttons = array_merge($buttons, $buttons_after);
		
		return $buttons;
	}

	/**
	 * Add the new TinyMCE plugin.
	 *
	 * @since 1.3
	 *
	 * @return array $plugin_array
	 */
	public static function multipage_mce_external_plugin( $plugin_array ) {
		$plugin_array['multipage'] = plugins_url( 'admin/tinymce/js/plugin.js', dirname( __FILE__ ) );
		return $plugin_array;
	}
	
	/**
	 * Add the new TinyMCE plugin locale.
	 *
	 * @since 1.3
	 *
	 * @return array $locales
	 */
	public static function multipage_mce_external_language( $locales ) {
		$locales ['multipage'] = plugin_dir_path ( __FILE__ ) . 'tinymce/languages.php';
		return $locales;
	}
	
	/**
	 * Add Multipage Plugin settings to the WordPress administration menu.
	 *
	 * @since 0.93
	 *
	 * @global Multipage_Plugin_Loader
	 * @global $submenu array submenu created for the menu slugs
	 * @return void
	 */
	public static function settings_menu_item() {
		global $multipage_plugin_loader;
		
		// main settings page
		if ( ! class_exists( 'Multipage_Plugin_Main_Settings' ) )
			require_once( dirname( __FILE__ ) . '/settings-main.php' );
		
		$menu_hook = Multipage_Plugin_Main_Settings::menu_item();
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
		 * @since 1.0
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
		 * @since 1.0
		 */
		do_action( 'nextpage_titles_settings_footer_' . $page_slug );
	}
}
?>