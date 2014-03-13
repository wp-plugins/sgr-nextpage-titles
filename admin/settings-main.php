<?php

/**
 * Display a settings page for Multipage Plugin
 *
 * @since 0.93
 */
class Multipage_Plugin_Main_Settings {
	/**
	 * Settings page identifier.
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'multipage-settings';
	
	/**
	 * Define our option array value.
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	const OPTION_NAME = 'multipage';

	/**
	 * The hook suffix assigned by add_submenu_page()
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	protected $hook_suffix = '';
	
	/**
	 * Initialize with an options array.
	 *
	 * @since 0.95
	 *
	 * @param array $options existing options
	 */
	public function __construct( $options = array() ) {
		if ( is_array( $options ) && ! empty( $options ) )
			$this->existing_options = $options;
		else
			$this->existing_options = array();
	}
	
	/**
	 * Add a menu item to WordPress admin.
	 *
	 * @since 0.93
	 *
	 * @uses add_utility_page()
	 * @return string page hook
	 */
	public static function menu_item() {
		$main_settings = new Multipage_Plugin_Main_Settings();

		$hook_suffix = add_options_page(
			__( 'Multipage Settings', 'sgr-npt' ), // page <title>
			'Multipage', // menu title
			'manage_options', // capability needed
			self::PAGE_SLUG, // what should I call you?
			array( &$main_settings, 'settings_page' ), // pageload callback
			'none' // to be replaced by sGR Nextpage Titles dashicon
		);
		
		// conditional load CSS, scripts
		if ( $hook_suffix ) {
			$main_settings->hook_suffix = $hook_suffix;
			register_setting( $hook_suffix, self::OPTION_NAME, array( 'Multipage_Plugin_Main_Settings', 'sanitize_options' ) );
			add_action( 'load-' . $hook_suffix, array( &$main_settings, 'onload' ) );
		}

		return $hook_suffix;
	}
	
	/**
	 * Load stored options and scripts on settings page view.
	 *
	 * @since 0.95
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

		add_action( 'nextpage_titles_settings_after_header_' . $this->hook_suffix, array( 'Multipage_Plugin_Main_Settings', 'after_header' ) );

		Multipage_Plugin_Settings::settings_page_template( $this->hook_suffix, __( 'Multipage Settings', 'sgr-npt' ) );
	}
	
	/**
	 * Multipages after header.
	 *
	 * @since 0.95
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

		// Multipages main settings
		$section = 'multipage';
		add_settings_section(
			$section,
			'', // no title for main section
			array( &$this, 'section_header' ),
			$this->hook_suffix
		);

		add_settings_field(
			'comments-oofp',
			__( 'Comments', 'sgr-npt' ),
			array( &$this, 'display_comments_oofp' ),
			$this->hook_suffix,
			'multipage',
			array( 'label_for' => 'comments-oofp' )
		);
		
		$section = 'toc';
		add_settings_section(
			$section,
			__( 'Table of contents', 'sgr-npt' ),
			array( &$this, 'section_header' ),
			$this->hook_suffix
		);

		add_settings_field(
			'toc-oofp',
			__( 'Only on the first page', 'sgr-npt' ),
			array( &$this, 'display_toc_oofp' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'toc-oofp' )
		);
		
		add_settings_field(
			'toc-position',
			_x( 'Position', 'Desired position of a the table of the contents.', 'sgr-npt' ),
			array( &$this, 'display_position' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'toc-position' )
		);
		
		add_settings_field(
			'toc-page-labels',
			_x( 'Page labels', 'Select which type of page labels to display.', 'sgr-npt' ),
			array( &$this, 'display_page_labels' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'toc-page-labels' )
		);
		
		add_settings_field(
			'toc-hide-header',
			__( 'Hide header', 'sgr-npt' ),
			array( &$this, 'display_hide_header' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'toc-hide-header' )
		);

		add_settings_field(
			'toc-comments-link',
			__( 'Comments link', 'sgr-npt' ),
			array( &$this, 'display_comments_link' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'toc-comments-link' )
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
	 * Display a checkbox to set if the comments must appear only on the first page of the post.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function display_comments_oofp() {
		$key = 'comments-oofp';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Show the comments only on the first page.', 'sgr-npt' ) );
		echo '</label>';
	}
	
	/**
	 * Display a checkbox to set if the table of contents must appear only on the first page of the post.
	 *
	 * @since 0.93
	 *
	 * @return void
	 */
	public function display_toc_oofp() {
		$key = 'toc-oofp';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Show the table of contents only on the first page of the post.', 'sgr-npt' ) );
		echo '</label>';
	}
	
	/**
	 * Where would you display the table of contents?
	 *
	 * @since 0.95
	 *
	 * @param array $extra_attributes custom form attributes
	 * @return void
	 */
	public function display_position( $extra_attributes = array() ) {
		$key = 'toc-position';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';
		
		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'post-toc-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );

		echo '<select name="' . esc_attr( $name ) . '" id="' . $id . '"';
		if ( isset( $class ) && $class )
			echo ' class="' . $class . '"';
		echo '>' . self::position_choices( isset( $this->existing_options[$key] ) ? $this->existing_options[$key] : '' ) . '</select>';
	}
	
	/**
	 * Describe page labels choices.
	 *
	 * @since 0.95
	 *
	 * @return array page labels descriptions keyed by page labels choice
	 */
	public static function page_labels_descriptions() {
		return array(
			'numbers' => __( 'Display numbers before the subpage title.', 'sgr-npt' ),
			'pages' => __( 'Display "Page #" before the subpage title.', 'sgr-npt' ),
			'hidden' => __( 'Hide subpage labels, display only the title.', 'sgr-npt' ),
		);
	}
	
	/**
	 * Which kind of page lables do you want to display?
	 *
	 * @since 0.95
	 *
	 * @param array $extra_attributes custom form attributes
	 * @return void
	 */
	public function display_page_labels( $extra_attributes = array() ) {
		$key = 'toc-page-labels';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = 'numbers';
		
		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'page-label-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );
		$name = esc_attr( $name );

		$descriptions = self::page_labels_descriptions();

		$page_labels_choices = self::$page_labels_choices;
		$choices = array();

		foreach( $page_labels_choices as $page_labels ) {
			$choice = '<label><input type="radio" name="' . $name . '" value="' . $page_labels . '"';
			$choice .= checked( $page_labels, $existing_value, false );
			$choice .= ' /> ';

			$choice .= $page_labels;
			if ( isset( $descriptions[$page_labels] ) )
				$choice .= esc_html( ' — ' . $descriptions[$page_labels] );
			$choice .= '</label>';

			$choices[] = $choice;
			unset( $choice );
		}

		if ( ! empty( $choices ) ) {
			echo '<fieldset id="' . $id . '"';
			if ( isset( $class ) && $class )
				echo ' class="' . $class . '"';
			echo '><div>';
			echo implode( '</div><div>', $choices );
			echo '</div></fieldset>';
		}
	}
	
	/**
	 * Display a checkbox to set if hide the table of contents header
	 *
	 * @since 0.93
	 *
	 * @return void
	 */
	public function display_hide_header() {
		$key = 'toc-hide-header';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Hide the table of contents header.', 'sgr-npt' ) );
		echo '</label>';
	}
	
	/**
	 * Display a checkbox to set if hide the table of contents header
	 *
	 * @since 0.93
	 *
	 * @return void
	 */
	public function display_comments_link() {
		$key = 'toc-comments-link';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Add a link for comments (only if comments are enabled).', 'sgr-npt' ) );
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
		
		// Main settings.
		if ( isset( $options['comments-oofp'] ) )
			$clean_options['comments-oofp'] = $options['comments-oofp'];
		else
			$clean_options['comments-oofp'] = 0;
		
		// Table of contents.
		if ( isset( $options['toc-oofp'] ) )
			$clean_options['toc-oofp'] = $options['toc-oofp'];
		else
			$clean_options['toc-oofp'] = 0;
			
		if ( isset( $options['toc-position'] ) && in_array( $options['toc-position'], self::$position_choices, true ) )
			$clean_options['toc-position'] = $options['toc-position'];
		else
			$clean_options['toc-position'] = 'bottom';
			
		if ( isset( $options['toc-page-labels'] ) && in_array( $options['toc-page-labels'], self::$page_labels_choices, true ) )
			$clean_options['toc-page-labels'] = $options['toc-page-labels'];
		else
			$clean_options['toc-page-labels'] = 'numbers';
			
		if ( isset( $options['toc-hide-header'] ) )
			$clean_options['toc-hide-header'] = $options['toc-hide-header'];
		else
			$clean_options['toc-hide-header'] = 0;
			
		if ( isset( $options['toc-comments-link'] ) )
			$clean_options['toc-comments-link'] = $options['toc-comments-link'];
		else
			$clean_options['toc-comments-link'] = 0;
		
		return $clean_options;
	}
	
	/**
	 * Place the table of contents above the post content, below the post content, or hide it.
	 *
	 * @since 0.95
	 *
	 * @var array
	 */
	public static $position_choices = array( 'bottom', 'top', 'hidden' );
	
	/**
	 * Choose the position of the table of contents above the post content, below the post content, or hide it.
	 *
	 * @since 0.95
	 *
	 * @param string $existing_value stored option value
	 * @return string HTML <option>s
	 */
	public static function position_choices( $existing_value = 'bottom' ) {
		if ( ! ( is_string( $existing_value) && $existing_value && in_array( $existing_value, self::$position_choices ) ) )
			$existing_value = 'bottom';

		$descriptions = array(
			'bottom' => __( 'after the content', 'sgr-npt' ),
			'top' => __( 'before the content', 'sgr-npt' ),
			'hidden' => __( 'hidden', 'sgr-npt' )
		);

		$options = '';
		foreach( self::$position_choices as $position ) {
			$options .= '<option value="' . $position . '"' . selected( $position, $existing_value, false ) . '>';
			if ( isset( $descriptions[$position] ) )
				$options .= esc_html( $descriptions[$position] );
			else
				$options .= $position;
			$options .= '</option>';
		}

		return $options;
	}
	
	/**
	 * Declare different page label styles.
	 *
	 * @since 0.95
	 *
	 * @var array
	 */
	public static $page_labels_choices = array( 'numbers', 'pages', 'hidden' );
	
	/**
	 * Choose different page label styles.
	 *
	 * @since 0.95
	 *
	 * @param string $existing_value stored option value
	 * @return string HTML <option>s
	 */
	public static function page_labels_choices( $existing_value = 'numbers' ) {
		if ( ! ( is_string( $existing_value) && $existing_value && in_array( $existing_value, self::$page_labels_choices ) ) )
			$existing_value = 'bottom';

		$descriptions = array(
			'bottom' => __( 'after the content', 'sgr-npt' ),
			'top' => __( 'before the content', 'sgr-npt' ),
			'hidden' => __( 'hidden', 'sgr-npt' )
		);

		$options = '';
		foreach( self::$page_labels_choices as $page_labels ) {
			$options .= '<option value="' . $page_labels . '"' . selected( $page_labels, $existing_value, false ) . '>';
			if ( isset( $descriptions[$page_labels] ) )
				$options .= esc_html( $descriptions[$page_labels] );
			else
				$options .= $page_labels;
			$options .= '</option>';
		}

		return $options;
	}
	
	/**
	 * Clean up custom form field attributes (fieldset, input, select) before use.
	 *
	 * @since 0.95
	 * @param array $attributes attributes that may possibly map to a HTML attribute we would like to use
	 * @param array $default_values fallback values
	 * @return array sanitized values unique to each field
	 */
	public static function parse_form_field_attributes( $attributes, $default_values ) {
		$attributes = wp_parse_args( (array) $attributes, $default_values );

		if ( ! empty( $attributes['id'] ) )
			$attributes['id'] = sanitize_html_class( $attributes['id'] );
		if ( ! empty( $attributes['class'] ) ) {
			$classes = explode( ' ', $attributes['class'] );
			array_walk( $classes, 'sanitize_html_class' );
			$attributes['class'] = implode( ' ', $classes );
		}

		return $attributes;
	}
}
?>