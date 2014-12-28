<?php

/*
Plugin Name: Multipage Plugin
Plugin URI: http://wordpress.org/plugins/sgr-nextpage-titles/
Description: Multipage Plugin for WordPress (formerly sGR Nextpage Titles) will give you the ability to order a post in multipages, giving each subpage a title and having a table of contents.
Author: Sergio De Falco aka SGr33n
Version: 1.2.3
Author URI: http://www.gonk.it/
*/

register_activation_hook( __FILE__			, array('Multipage_Plugin_Loader', 'activate_plugin') );									// Registering plugin activation hook.
register_deactivation_hook( __FILE__		, array('Multipage_Plugin_Loader', 'deactivate_plugin') );									// Registering plugin deactivation hook.

/**
 * Load the Multipage Plugin
 *
 * @since 0.6
 */
class Multipage_Plugin_Loader {
	/**
	 * Uniquely identify plugin version
	 * Bust caches based on this value
	 *
	 * @since 0.6
	 * @var string
	 */
	const VERSION = '1.2.2';
	
	/**
	 * Store Multipage default settings.
	 *
	 * @since 1.1
	 *
	 * @var array {}
	 */
	public $multipage_settings_defaults = array( 'comments-oofp' => 0, 'toc-oofp' => 0, 'toc-hide-header' => 0, 'toc-page-labels' => 'numbers', 'toc-comments-link' => 0, 'toc-position' => 'bottom' );
	
	/**
	 * Let's get it started
	 *
	 * @since 1.0
	 */
	public function __construct() {
		// load plugin files relative to this directory
		$this->plugin_directory = dirname(__FILE__) . '/';

		// Load the textdomain for translations
		load_plugin_textdomain( 'sgr-npt', true, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		// load shortcodes
		if ( ! class_exists( 'Multipage_Plugin_Shortcodes' ) )
			require_once( $this->plugin_directory . 'classes/shortcodes.php' );
		Multipage_Plugin_Shortcodes::init();
		
		if ( is_admin() ) {
			$this->admin_init();
		} else {
			add_action( 'wp', array( &$this, 'public_init' ) );
		}
	}
	
	/**
	 * Handles actions for the plugin activation
	 *
	 * @since 0.6
	 */
	static function activate_plugin() {
	}
	
	/**
	 * Handles actions for the plugin deactivation
	 *
	 * @since 0.6
	 */
	static function deactivate_plugin() {
	}
	
	/**
	 * Intialize the public.
	 *
	 * @since 1.0
	 */
	public function public_init() {
		global $post;
		
		// no need to process
		if ( is_feed() || is_404() || empty( $post ) )
			return;

		// Variables
		$page = get_query_var( 'page' );
		$content = $post->post_content;
		$pattern = "/\[nextpage[^\]]*\]/";
		$post_subpages = array();
		$p = 0;

		preg_match_all( $pattern, $content, $matches );
		foreach ( $matches[0] as $match ) {
			$atts = shortcode_parse_atts( str_replace(array( '[', ']' ), '', $match) );

			// Check if the intro has a Title
			if ( 0 == $p && 0 != strpos( $content, $match ) ) :
				$post_subpages['title'][] = __( 'Intro', 'sgr-npt' );
				$post_subpages['scroll_to_toc'][] = false;
				$current_title = $atts["title"];
				$p++;
			else :
				$current_title = $atts["title"];
			endif;
		
			$post_subpages['title'][] = $current_title;
			$post_subpages['scroll_to_toc'][] = in_array( 'toc', $atts ) ? true : false;
			$p++;
		}
		
		// If there aren't subpages or it's a loop, exit.
		// Use is_singular because it looks for every post.
		if ( empty( $post_subpages ) )
			return;
		
		// If the requested page doesn't exist (even if there is a declared page=1 variable).
		// return 404.
		//if ( $page == 1 || $page > $p )
			//$this->return_404();
		
		// Update $post Object with new data.
		$post->post_content = preg_replace( $pattern, '<!--nextpage-->', $content );
		$post->post_subpages = $post_subpages;
		
		add_action( 'wp_enqueue_scripts',	array( &$this, 'enqueue_styles' ) );
		add_filter( 'wp_link_pages_args',	array( &$this, 'hide_standard_pagination' ) );
		add_filter( 'wp_title',				array( &$this, 'enhance_title' ), 30 );
		add_filter( 'the_content', 			array( &$this, 'enhance_content' ), 20 );
	}

	/**
	 * Initialize the backend
	 *
	 * @since 0.93
	 */
	public function admin_init() {
		$admin_dir = $this->plugin_directory . 'admin/';

		// Multipage Plugin settings loader
		if ( ! class_exists( 'Multipage_Plugin_Settings' ) )
			require_once( $admin_dir . 'settings.php' );
		Multipage_Plugin_Settings::init();
	}
	
	/**
	 * Styles applied to public-facing pages
	 *
	 * @since 0.6
	 * @uses enqueue_styles()
	 */
	public static function enqueue_styles() {
	
		// LTR or RTL
		$file = is_rtl() ? 'css/multipage-rtl' : 'css/multipage';
		
		// Minimized version or not
		$file .= ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min' ) . '.css';

		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() );
			$handle   = 'child-multipage';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() );
			$handle   = 'parent-multipage';

		// Multipage Plugin Theme Compatibility
		} else {
			$location = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'static/';
			$handle   = 'default-multipage';
		}

		// Enqueue the Multipage Plugin styling
		wp_enqueue_style( $handle, $location . $file, array(), self::VERSION, 'screen' );
	}
	
	/**
	 * Add the subpages summary and other stuff to the_content.
	 *
	 * @since 1.0
	 */
	public function enhance_title( $title ) {
		global $post;
		
		// Get the page number - ToDo: create a function to retrieve the page number
		$page = ( get_query_var('page') ) ? get_query_var('page') : 1;
		
		// If it is the the first page.
		if ( $page <= 1 )
			return $title;

		// Correct the page number for over pages, consistent with the behavior of WordPress.
		if ( $page > max( array_map( 'count', $post->post_subpages ) ) )
			$page = 1;
		
		// If it is the the first page.
		if ( $page <= 1 )
			return $title;

		$subpages = $post->post_subpages;
		$subpage_title = $subpages['title'][ $page -1 ];
		
		// Eventually, manipulate WordPress SEO by Yoast custom title. Note that WP SEO counts +1 pages than real, investigate on this
		// for now I added +1 to the following expression in order to make it work.
		$title = str_replace( sprintf( __( 'Page %d of %d', 'wordpress-seo' ), $page, max( array_map( 'count', $subpages ) ) + 1 ), $subpage_title, $title );
		
		// Manipulate Theme standard title.
		$title = str_replace( sprintf( __( 'Page %s', wp_get_theme()->get( 'TextDomain' ) ), $page ), $subpage_title, $title );

		return $title;		
	}

	/**
	 * Add the subpages summary and other stuff to the_content.
	 *
	 * @since 1.0
	 */
	public function enhance_content( $content ) {
		global $post;
		
		// Table of contents should not be the only content in the post.
		if ( ! $content )
			return $content;
			
		if ( ! is_singular() )
			return $content;
			
		if ( ! property_exists( $post, 'post_subpages' ) )
           return $content;
		   
		// Get the page number - ToDo: create a function to retrieve the page number
		$page = ( get_query_var('page') ) ? get_query_var('page') : 1;
		
		// Correct the page number for overflow pages, consistent with the behavior of WordPress.
		if ( $page > max( array_map( 'count', $post->post_subpages ) ) )
			$page = 1;
			
		// Get options with default values.
		$options = get_option( 'multipage', $this->multipage_settings_defaults );
		if ( ! is_array( $options ) )
			$options = array();
		
		// If not the first page, hide comments.
		if ( $page != 1 && $options['comments-oofp'] )
			add_filter( 'comments_template', array( &$this, 'hide_comments' ) );

		$subpages = $post->post_subpages;
		$subtitle = '<h2 class="entry-subtitle">' . apply_filters( 'multipage_subtitle', $subpages['title'][ $page -1 ] ) . '</h2>';
	
		if ( $page >= max( array_map( 'count', $subpages ) ) ) {
			$multipagenav = '<div class="multipage-navlink">' . __( 'Back to: ', 'sgr-npt' ) . ' <a rel="index" href="' . get_permalink() . '">' . $subpages['title'][ 0 ] . '</a></div>';
		} else {
			$multipagenav = '<div class="multipage-navlink">' . __( 'Continue:', 'sgr-npt' ) . ' <a rel="next" href="' . $this->get_subpage_link( $page +1 ) . '">' . $subpages['title'][ $page ] .'</a></div>';
		}
		
		$multipagenav = apply_filters( 'multipage_navigation', $multipagenav );
		
		$enhanced_content = $subtitle . $content . $multipagenav;

		if ( ! $options['toc-oofp'] || $page == 1 ) {
		
			$toc = '<ul id="toc" class="multipage-toc multipage-toc-' . $post->ID . '">';
			if ( ! $options['toc-hide-header'] )					
				$toc .= '<li class="toc-header">' . __( 'Contents', 'sgr-npt' ) . '</li>';
						
			foreach ( $subpages as $b ) {
				foreach ( $b as $c => $match ) {
				
					$current = $c+1;
					$toc .= '<li class="subpage-' . $current;
					if ( $current == $page )
						$toc .= ' current';
					$toc .= '">';
					
					// Subpage label.
					if ( $options['toc-page-labels'] === 'numbers' ) {
						$toc .= '<span class="numbers">' . $current . '. </span> ';	
					}
					elseif ( $options['toc-page-labels'] === 'pages' ) {
						$toc .= '<span class="pages">' . sprintf( __( 'Page %d:', 'sgr-npt' ), $current ) . '</span> ';
					}
					
					// Subpage link.
					$toc .= '<a href="' . $this->get_subpage_link( $current, $subpages['scroll_to_toc'][ $c ] ) . '">' . $subpages['title'][ $c ] . '</a></li>';
				}
				break;
			}
		
			// If comments are open add the link to the table of contents.
			if ( comments_open() && $options['toc-comments-link'] )		
				$toc .= '<li class="toc-footer"><a href="' . get_comments_link()  . '">' . sprintf( __( 'Comments (%d)', 'sgr-npt' ), get_comments_number() ) . '</a></li>';
				
			$toc .= '</ul><!-- #multipage-toc-' . $post->ID . '-->';
			
			if ( $options['toc-position'] === 'top' ) {
				$enhanced_content = $toc . $enhanced_content;
			}
			elseif ( $options['toc-position'] === 'bottom' ) {
				$enhanced_content .= $toc;
			}
		}

		return apply_filters( 'multipage_content', $enhanced_content );
	}
	
	/**
	 * Retrieve the subpage permalink.
	 *
	 * @since 1.0
	 */
	private function get_subpage_link( $page, $scroll_to_toc = false ) {
		$base = get_permalink();

		// If it's the first page the link is the base permalink
		if ( $page < 2 )
			return $base;
		
		if ( ! get_option('permalink_structure') || is_admin() || true == get_query_var('preview') )
			return add_query_arg( array('page' => $page ) );
		
		$subpage_link = trailingslashit( $base ) . user_trailingslashit( $page, 'page' );
		
		// Add the scroll_to_toc to table of content link
		if ( $scroll_to_toc == true )
			$subpage_link .= "#toc";

		return $subpage_link;
	}

	/**
	 * Return a 404 page.
	 *
	 * @since 0.9
	 */
	private function return_404() {
		global $wp_query;
		
		$wp_query->set_404();
		status_header(404);
		return;
	}
	
	/**
	 * Hide the standard pagination.
	 *
	 * @since 0.6
	 */
	public static function hide_standard_pagination( $args ) {
		$args['echo'] = 0;
		return $args;
	}
	
	/**
	 * Hide comments area.
	 *
	 * @since 1.0
	 */
	function hide_comments() {
		// Return an empty file.
		return dirname( __FILE__ ) . '/index.php';
	}
}

/**
 * Load plugin function during the WordPress init action
 *
 * @since 0.6
 */
function multipage_plugin_loader_init() {
	global $multipage_plugin_loader;

	$multipage_plugin_loader = new Multipage_Plugin_Loader();
}
add_action( 'init', 'multipage_plugin_loader_init', 0 ); // load before widgets_init at 1
