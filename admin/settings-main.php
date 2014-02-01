<?php

/**
 * Display a settings page for sGR Nextpage Titles
 *
 * @since 0.93
 */
class Nextpage_Titles_Main_Settings {
	/**
	 * Settings page identifier.
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'npt-main-settings';
	
	/**
	 * Define our option array value.
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	const OPTION_NAME = 'nextpage_titles_main';
	
	/**
	 * Define the summary only on the first page option value.
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	const OPTION_NAME_SUMMARY_OOFP = 'npt_summary_oofp';
	
	/**
	 * Define the display page labels option value.
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	const OPTION_NAME_SUMMARY_PAGE_LABELS = 'npt_summary_page_labels';
	
	/**
	 * The hook suffix assigned by add_submenu_page()
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	protected $hook_suffix = '';
	
	/**
	 * Add a menu item to WordPress admin.
	 *
	 * @since 0.93
	 *
	 * @uses add_utility_page()
	 * @return string page hook
	 */
	public static function menu_item() {
		$main_settings = new Nextpage_Titles_Main_Settings();

		$hook_suffix = add_options_page(
			__( 'Nextpage Titles Configuration', 'sgr-npt' ), // page <title>
			'Nextpage Titles', // menu title
			'manage_options', // capability needed
			self::PAGE_SLUG, // what should I call you?
			array( &$main_settings, 'settings_page' ), // pageload callback
			'none' // to be replaced by sGR Nextpage Titles dashicon
		);
		
		// conditional load CSS, scripts
		if ( $hook_suffix ) {
			$main_settings->hook_suffix = $hook_suffix;
			register_setting( $hook_suffix, self::OPTION_NAME, array( 'Nextpage_Titles_Main_Settings', 'sanitize_options' ) );
			add_action( 'load-' . $hook_suffix, array( &$main_settings, 'onload' ) );
		}

		return $hook_suffix;
	}
	
	/**
	 * Load stored options and scripts on settings page view.
	 *
	 * @since 1.1
	 *
	 * @uses get_option() load existing options
	 * @return void
	 */
	public function onload() {
		$options = get_option( self::OPTION_NAME );
		if ( ! is_array( $options ) )
			$options = array();
		$this->existing_options = $options;

		$this->settings_api_init();

		//add_action( 'admin_enqueue_scripts', array( 'Facebook_Application_Settings', 'enqueue_scripts' ) );
	}
	
	/**
	 * Load the settings page.
	 *
	 * @since 0.93
	 *
	 * @return void
	 */
	public function settings_page() {
		if ( ! isset( $this->hook_suffix ) )
			return;

		add_action( 'nextpage_titles_settings_after_header_' . $this->hook_suffix, array( 'Nextpage_Titles_Main_Settings', 'after_header' ) );

		Nextpage_Titles_Settings::settings_page_template( $this->hook_suffix, __( 'Nextpage Titles for WordPress Configuration', 'sgr-npt' ) );
	}
	
	/**
	 * Facebook Like Button after header.
	 *
	 * @since 1.1
	 *
	 * @return void
	 */
	public static function after_header() {
		// Facebook Like Button
		echo ""; /* Inserire qui il pulsante Facebook */
	}
	
	/**
	 * Hook into the settings API.
	 *
	 * @since 0.93
	 *
	 * @uses add_settings_section()
	 * @uses add_settings_field()
	 * @return void
	 */
	private function settings_api_init() {
		if ( ! isset( $this->hook_suffix ) )
			return;

		// Facebook application settings
		$section = 'npt-main';
		add_settings_section(
			$section,
			__( 'Summary appearance', 'sgr-npt' ),
			array( &$this, 'section_header' ),
			$this->hook_suffix
		);

		add_settings_field(
			'npt-summary-oofp',
			__( 'Display only on the first page', 'sgr-npt' ),
			array( 'Nextpage_Titles_Main_Settings', 'display_summary_oofp' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'npt-summary-oofp' )
		);
		
		add_settings_field(
			'npt-summary-page-labels',
			__( 'Display page labels', 'sgr-npt' ),
			array( 'Nextpage_Titles_Main_Settings', 'display_page_labels' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'npt-page-labels' )
		);
	}
	
	/**
	 * Introduction to the main settings section.
	 *
	 * @since 0.93
	 *
	 * @return void
	 */
	public function section_header() {
		//echo "";
	}
	
	/**
	 * Display a checkbox to set if the summary must appear only on the first page
	 *
	 * @since 0.93
	 *
	 * @global Nextpage_Titles_Loader $nextpage_titles_loader determine old status
	 * @return void
	 */
	public static function display_summary_oofp() {
		global $nextpage_titles_loader;

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[summary_oofp]" id="npt-summary-oofp" value="0"';
		checked( $nextpage_titles_loader->summary_oofp );
		echo ' /> ';
		echo esc_html( __( 'Do you want to display the summary only on the first page of the post?', 'sgr-npt' ) );
		echo '</label>';
	}
	
	/**
	 * Display a checkbox to set if the display page labels (ex. Page 1)
	 *
	 * @since 0.93
	 *
	 * @global Nextpage_Titles_Loader $nextpage_titles_loader determine old status
	 * @return void
	 */
	public static function display_page_labels() {
		global $nextpage_titles_loader;

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[summary_page_labels]" id="npt-summary-page-labels" value="0"';
		checked( $nextpage_titles_loader->summary_page_labels );
		echo ' /> ';
		echo esc_html( __( 'Do you want to display page labels before the page title?', 'sgr-npt' ) );
		echo '</label>';
	}
	
	/**
	 * Clean user inputs before saving to database.
	 *
	 * @since 0.93
	 *
	 * @param array $options form options values
	 * @return array $options sanitized options
	 */
	public static function sanitize_options( $options ) {
		// start fresh
		$clean_options = array();

		if ( isset( $options['summary_oofp'] ) )
			update_option( self::OPTION_NAME_SUMMARY_OOFP, '1' );
		else
			delete_option( self::OPTION_NAME_SUMMARY_OOFP );
			
		if ( isset( $options['summary_page_labels'] ) )
			update_option( self::OPTION_NAME_SUMMARY_PAGE_LABELS, '1' );
		else
			delete_option( self::OPTION_NAME_SUMMARY_PAGE_LABELS );
			
		return $clean_options;
	}
}
?>