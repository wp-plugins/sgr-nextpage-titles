<?php

/*
Plugin Name: SGR Nextpage Titles
Plugin URI: http://www.gonk.it/
Description: A plugin that replaces (but not disables) the <code>&lt;!--nextpage--&gt;</code> code and gives the chance to have subtitles for your post subpages. You will have also an index, reporting all subpages, and pretty urls. 
Author: Sergio De Falco aka SGr33n
Version: 0.6.3
Author URI: http://www.gonk.it/
*/

register_activation_hook(__FILE__    	, array('Nextpage_Titles_Loader', 'install_plugin'));		// Registering plugin activation hook.
register_deactivation_hook( __FILE__    , array('Nextpage_Titles_Loader', 'uninstall_plugin'));		// Registering plugin deactivation hook.

/**
 * Load the SGR Nextpage Title default option values
 *
 * @since 0.6
 */
require_once( dirname(__FILE__) . '/config.php' ); 

/**
 * Load the SGR Nextpage Title plugin
 *
 * @since 1.1
 */
class Nextpage_Titles_Loader {
	/**
	 * Uniquely identify plugin version
	 * Bust caches based on this value
	 *
	 * @since 0.6
	 * @var string
	 */
	const VERSION = '0.6.3';

	/**
	 * Let's get it started
	 *
	 * @since 0.6
	 */
	public function __construct() {
		// load plugin files relative to this directory
		$this->plugin_directory = dirname(__FILE__) . '/';

		// Load the textdomain for translations
		load_plugin_textdomain( 'sgr-npt', true, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	
		// load shortcodes
		if ( ! class_exists( 'Nextpage_Titles_Shortcodes' ) )
			require_once( $this->plugin_directory . 'classes/shortcodes.php' );
		Nextpage_Titles_Shortcodes::init();
		
		add_action( 'wp', array( &$this, 'public_init' ) );
		add_filter('query_vars', array( 'Nextpage_Titles_Loader', 'add_paget_query_var' ) );
	}
	
	/**
	 * End
	 *
	 * @since 0.6
	 */
	public function __deconstruct() {
	}
	
	/**
	 * Handles actions for the plugin activation
	 *
	 * @since 0.6
	 */
	static function install_plugin() {
		// Load new rewrite rules & flush
		Nextpage_Titles_Loader::add_paget_rewrite_rule();
		flush_rewrite_rules();
	}
	
	/**
	 * Handles actions for the plugin deactivation
	 *
	 * @since 0.6
	 */
	static function uninstall_plugin() {
		// Flush rewrite rules
		flush_rewrite_rules();
	}
	
	/**
	 * Intialize the public.
	 *
	 * @since 0.6
	 */
	public function public_init() {
		// no need to process
		if ( is_feed() || is_404() || true == get_query_var('preview') )
			return;
			
		$prettyurl = false;
			
		if ( 'post' == get_post_type() )
			$prettyurl = true;
		
		global $post, $post_pages, $wp_rewrite;
		
		// Variables
		$content = $post->post_content;
		$page = (get_query_var('page')) ? get_query_var('page') : 1;
		$pattern = "/\[nextpage[^\]]*\]/";
		$post_pages = array();
		$p = 0;
		
		preg_match_all($pattern, $content, $matches);
		foreach ($matches[0] as $match) {
			$p++;
			$current_title = $this->get_nextpage_title($match, $p);
			$post_pages[] = $current_title;
		}
		$post->post_content = preg_replace($pattern, '<!--nextpage-->', $content);
		
		if ( $prettyurl == true ) {
			$paget = str_replace( "/subpage-", "", (get_query_var('paget')) ? get_query_var('paget') : '' );
			// If there is the query var for the title, convert it into page number
			if ( $paget )
				$page = $this->get_nextpage_pagenum( $paget, $post_pages );
		}

		// If there are not subpages exit
		if ( empty($post_pages) )
			return;
		
		add_action( 'the_post', array( 'Nextpage_Titles_Loader', 'set_npt_pagenum' ) );
		add_action( 'wp_enqueue_scripts', array( 'Nextpage_Titles_Loader', 'enqueue_styles' ) );
		add_filter( 'wp_link_pages_args', 'sgrnpt_next_prev' );
		add_filter( 'the_content', 'add_to_the_content', 10 );
	}
		
	/**
	 * Retrieve the subpage title. If it has no title, use a generic one.
	 *
	 * @since 0.6
	 */
	private function get_nextpage_title($code, $pagen) {
		$pattern = '/title=(["\'])(.*?)\1/';
		$count = preg_match($pattern, $code, $matches);
		if ($count)
			return $matches[2];
		else
			return "Page $pagen";
	}

	/**
	 * Enable the paget query var to pass the title in place of the page number
	 *
	 * @since 0.6
	 */
	public static function add_paget_query_var($public_query_vars) {
		$public_query_vars[] = 'paget';
		return $public_query_vars;
	}

	/**
	 * Retrieve the number of the page from the paget var.
	 *
	 * @since 0.6
	 * @var string, array
	 */
	public function get_nextpage_pagenum($paget, $post_pages = array() ) {
	
		$p = 0;	
		foreach ($post_pages as $post_page) {
			$p++;
			if ( sanitize_title($post_page) == $paget )
				return $p +1;
		}
		return 0;
	}
	
	/**
	 * Set the current page on the nextpage title page number
	 *
	 * @since 0.6
	 */
	public function set_npt_pagenum() {
		global $page, $post_pages;

		$paget = str_replace("/subpage-", "", (get_query_var('paget')) ? get_query_var('paget') : '');
		if ( $paget )
			$page = Nextpage_Titles_Loader::get_nextpage_pagenum( $paget, $post_pages );	
	}
	
	/**
	 * Styles applied to public-facing pages
	 *
	 * @since 0.6
	 * @uses enqueue_styles()
	 */
	public static function enqueue_styles() {
		wp_enqueue_style( 'nextpage-titles', plugins_url( 'css/default' . ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min' ) . '.css', __FILE__ ), array(), self::VERSION );
	}
	
	/**
	 * Enable rewrite rule for paget query var	 
	 *
	 * @since 0.6
	 */
	public function add_paget_rewrite_rule() {
		//add_rewrite_rule( '(.?.+?)(/subpage-[^/]+)?/?$', 'index.php?name=$matches[1]&paget=$matches[2]', 'top'); /* for pages */
		add_rewrite_rule( '([0-9]{4})/([0-9]{1,2})/([^/]+)(/subpage-[^/]+)?/?$', 'index.php?year=$matches[1]&monthnum=$matches[2]&name=$matches[3]&paget=$matches[4]', 'top');
	}
}

/**
 * Add the subpages summary and other stuff to the_content.
 *
 * @since 0.6
 */
function add_to_the_content( $content ) {
	
	// Summary should not be the only content in the post
	if ( ! $content )
		return $content;
	
	global $post, $post_pages;
	
	$p = 1;
	$subtitle = '';
	$page = (get_query_var('page')) ? get_query_var('page') : 1;
	$paget = str_replace("/subpage-", "", (get_query_var('paget')) ? get_query_var('paget') : '');

	if( $paget )
		$page = Nextpage_Titles_Loader::get_nextpage_pagenum( $paget, $post_pages );
	
	if ( $page > 1 )
		$subtitle = '<h2 class="entry-subtitle">' . $post_pages[ $page -2 ] . '</h2>';

	if ( $page > count( $post_pages ) ) :
		$navlink = '
			<div class="back-link">' . __( 'End: ', 'sgr-npt' ) . ' <a href="' . get_permalink( $post->ID ) . '">' . __( 'Back to intro', 'sgr-npt' ) .'</a></div>';
	else :
		$navlink = '
			<div class="continue-link">' . __( 'Continue:', 'sgr-npt' ) . ' <a href="' . get_pagetitle_link( $post->ID, $page +1, $post_pages[ $page -1 ] ) . '">' . $post_pages[$page -1] .'</a></div>';
	endif;
	
	$summary = '
		<ul id="sgr-npt-summary-' . $post->ID . '" class="sgr-npt-summary">
			<li class="subpage-' . $p . '"><span>' . sprintf( __( 'Page %d:', 'sgr-npt' ), $p ) . '</span>
			<a href="' . get_permalink( $post->ID ) . '">' . __( 'Intro', 'sgr-npt' ) . '</a></li>';

	foreach ($post_pages as $match) {
		$p++;
		$summary .= '
			<li class="subpage-' . $p . '"><span>' . sprintf( __( 'Page %d:', 'sgr-npt' ), $p ) . '</span>
			<a href="' . get_pagetitle_link( $post->ID, $p, $post_pages[ $p -2 ] ) . '">' . $match . '</a></li>';
	}

	$summary .= '</ul><!-- #sgr-npt-summary-' . $post->ID . '-->';

	// Return the content
	return $subtitle . $content . $navlink . $summary;
}

function get_pagetitle_link( $postid, $pagenum = 1, $paget = '' ) {
	$base = get_permalink( $postid );
	
	if ( !get_option('permalink_structure') || is_admin() )
		return add_query_arg( array('paget' => sanitize_title($paget) ), $base );
		//return add_query_arg( array('page' => $pagenum), $base ); /* this is if you want number instead of pretty links */
	
	// If not post post_type, disable pretty url
	if ( 'post' != get_post_type() )
		return $base . user_trailingslashit( $pagenum, 'page' );

	return $base . user_trailingslashit( 'subpage-' . sanitize_title($paget), 'paget' );
}

/**
 * Hide the standard pagination.
 *
 * @since 0.6
 */
function sgrnpt_next_prev($args) {
	$args['echo'] = 0;
	return $args;
}

/**
 * Load plugin function during the WordPress init action
 *
 * @since 0.6
 */
function nextpage_titles_loader_init() {
	global $nextpage_titles_loader;

	$nextpage_titles_loader = new Nextpage_Titles_Loader();
}
add_action( 'init', 'nextpage_titles_loader_init', 0 ); // load before widgets_init at 1