<?php

/**
 * Functions running after the plugin activation.
 */
function sgrntp_activate() {
	global $wp_rewrite;

	// Add new rewrite rules
	add_action( 'generate_rewrite_rules', 'sgrnpt_rewrite_rules' );
	
	// Flush rewrite rules
	$wp_rewrite->flush_rules();
}

/**
 * Functions running after the plugin deactivation.
 */
function sgrntp_deactivate() {
	global $wp_rewrite;

	// Reset rewrite rules
	$wp_rewrite->set_permalink_structure( get_option('permalink_structure') );
	
	// Flush rewrite rules
	$wp_rewrite->flush_rules();
}

/**
 * Functions running during the plugin intialization.
 */
function sgrnpt_plugin_init() {
	global $pagenow;
	
	// Load plugin styles
	if ( !is_admin() && 'wp-login.php' != $pagenow ) {
		wp_enqueue_style('sgrnpt', SGRNPT_PLUGIN_URL . SGRNPT_CSS_FOLDER . 'default.css');
	}
	
	// Add new rewrite rules
	add_action( 'generate_rewrite_rules', 'sgrnpt_rewrite_rules' );
	
	// Add filters & actions
	add_filter( 'query_vars', 'sgrnpt_query_vars' );
	add_action( 'get_header', 'sgrnpt_get_header' );
}

/**
 * Functions running during the wp_head.
 */
function sgrnpt_get_header() {
	global $post, $wp_rewrite;
	
	$id = (int) $post->ID;
	$content = $post->post_content;
	
	if ( is_single() ) {
		
		$paget = (get_query_var('paget')) ? get_query_var('paget') : '';
		
		$pages = get_nextpage_shortcode($content);
		$numpages = count($pages);
		$index = "";
		$pagelinks = "";
		$title = "";
		$titlenext = "";
		$titleprev = "";
		$c = 0;
		
		if ($numpages) {

			// Look for the first shortcode and save it
			$next = get_nextpage_shortcode($content, true);
			if ($next) $content = substr($content, 0, strpos($content, $next));	
		
			// Before the start of the loop
			$index .= '<ul id="sgrnpt-post-' . $post->ID . '" class="sgrnpt-table">';
			
			foreach ($pages as $page) {
				if ( ( '' == $titlenext ) && !$paget ) $titlenext = $page['title'];
				$pagelink = get_pagetitle_link($page['title']);
				$index .= '<li><a href="' . $pagelink . '">' . $page['title'] . '</a></li>';
				//$index .= '<li>' . get_pagetitle_link($page['title']) . '</li>';
				if ( sanitize_title( $page['title'] ) == $paget) {
					$content = $page['content'];
					$title = $page['title'];
				} elseif ( ( '' == $title ) && ( '' != $paget ) ) {
					$titleprev = $page['title'];
				} elseif ( ( '' != $title ) && ( '' == $titlenext ) ) {
					$titlenext = $page['title'];
				}
			}
			
			// After the end of the loop
			$index .= '</ul>';
						
			$pagelinks .= '<div class="page-link">';
			
			if ( $paget && !$titleprev ) {
				$titleprev = __( 'Back to summary', 'sgr-nextpage-titles' );
				$pagelinkprev = get_permalink( $post->ID );
			} else {
				$pagelinkprev = get_pagetitle_link( sanitize_title( $titleprev ) );
			}

			// If there is a next page
			if ( '' != $titlenext ){
					
				$pagelinknext = get_pagetitle_link( sanitize_title( $titlenext ) );
				$pagelinks .= '<a href="' . $pagelinknext . '" class="linknext">' . $titlenext . ' &raquo;</a>';
			}

			// If there is a prev page
			if ( '' != $titleprev ) {

				$pagelinks .= '<a href="' . $pagelinkprev . '" class="linkprev">&laquo; ' . $titleprev . '</a>';
			} else {
				
				$pagelinks .= '<div class="linknext">&nbsp;</div>';
			}

			$pagelinks .= '</div>';
		}

		if ( $title ) $post->post_title = $post->post_title . ": " . $title;
		$post->post_content = $index . $content . $pagelinks;
		
		return true;
	} else {

		// Look for the first shortcode and save it
		$next = get_nextpage_shortcode($content, true);
		if ($next) $content = substr($content, 0, strpos($content, $next));	
		
		$post->post_content = $content;
		return true;
	}
}
/**
 * A function to retrieve the subpage title. If it has no title, use a generic one.
 */
function get_nextpage_title($code, $pagen) {
	$pattern = '/title=(["\'])(.*?)\1/';
	$count = preg_match($pattern, $code, $matches);
	if ($count)
		return $matches[2];
	else
		return "Page $pagen";
}

/**
 * A function to retrieve the subpage content.
 */
function get_nextpage_content($code, $post, $pagen) {

	$pos = strpos($post, $code);
	$len = strlen($code);
	$part = substr($post, $pos+$len);
	$next = get_nextpage_shortcode($part, true);
	
	// Look for the next shortcode
	if ($next) {
	
		// Return the part before the next shortcode
		$pos = strpos($part, $next);
		$part = substr($part, 0, $pos);
	}
	return $part;
}

/**
 * Check for nextpage shortcodes.
 */
function get_nextpage_shortcode($post, $next = false) {
	
	// Variables declaration
	$pattern = "/\[nextpage[^\]]*\]/";
	$p = 0;
	
	// Returns the next shortcode
	if ( $next == true ) {
	
		preg_match($pattern, $post, $matches);
		if ( array_key_exists( 0, $matches ) )  return $matches[0];
	
	// Returns all shortcodes
	} else {
	
		preg_match_all($pattern, $post, $matches);
		foreach ($matches[0] as $match) {
			$p++;
			$title = get_nextpage_title($match, $p);
			$page['number'] = $p;
			$page['title'] = $title;
			$page['content'] = get_nextpage_content($match, $post, $p);
			$pages[] = $page;
		}
		return $pages;	
	}
}

/**
 * Register the variables that will be used as parameters on the url.
 */
function sgrnpt_query_vars($public_query_vars) {

	// Setting the variable int the array
	$public_query_vars[] = 'paget';

    return $public_query_vars;
}

/**
 * Retrieve links for subpages.
 */
function get_pagetitle_link($pagetitle, $escape = true ) {
	global $wp_rewrite, $post;

	$request = remove_query_arg( 'paget' );

	if ( !$wp_rewrite->using_permalinks() || is_admin() ) {
		
		$base = get_bloginfo( 'url' );
		
		if ( $pagetitle ) {
			$result = add_query_arg( 'paget', sanitize_title($pagetitle), $base . $request );
		} else {
			$result = $base . $request;
		}
	} else {
	
		$permalink = get_permalink($post->ID);

		if ( '' != $pagetitle ) {
			$request = user_trailingslashit( sanitize_title($pagetitle), 'paget' );
		}

		$result = $permalink . $request;
	}

	if ( $escape )
		return esc_url( $result );
	else
		return esc_url_raw( $result );
}

function get_nextpage_prev_link() {
	global $post;
}

function get_nextpage_next_link() {
	global $post;
}

/**
 * Create nextpage rewrite rules.
 */
function sgrnpt_rewrite_rules() {
	global $wp_rewrite;

	// Define custom rewrite tokens
	$rewrite_tag = '%paget%';

	// Add the rewrite tokens
	$wp_rewrite->add_rewrite_tag( $rewrite_tag, '(.+?)', 'paget=' );

	// Define the custom permalink structure
	$rewrite_keywords_structure = $wp_rewrite->root . get_option('permalink_structure') . $rewrite_tag . "/?";

	// Generate the rewrite rules
	$new_rule = $wp_rewrite->generate_rewrite_rules( $rewrite_keywords_structure, false );

	// Add the new rewrite rule into the global rules array
	$wp_rewrite->rules = $new_rule + $wp_rewrite->rules;

	return $wp_rewrite->rules;
}
