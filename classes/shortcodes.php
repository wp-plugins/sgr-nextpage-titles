<?php
/**
 * Convert shortcodes
 *
 * @since 0.6
 */
class Nextpage_Titles_Shortcodes {

	/**
	 * Register shortcode handlers
	 *
	 * @since 1.1
	 */
	public static function init() {
		add_shortcode( 'nextpage', array( 'Nextpage_Titles_Shortcodes', 'nextpage' ) );
	}
	
	/**
	 * Generate HTML elements
	 *
	 * @since 0.6
	 * @param array $attributes shortcode attributes. overrides site options for specific button attributes
	 * @param string $content shortcode content. no effect
	 */
	public static function nextpage( $attributes, $content = null ) {

		return '<hr>';
	}
}
?>