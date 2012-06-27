<?php

/**
 * Functions running after the plugin activation.
 */
function sgrntp_activate() {
	global $wp_rewrite;

	// Add new rewrite rules
	add_action( 'generate_rewrite_rules', 'nextpage_rewrite_rules' );
	
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
	add_action( 'generate_rewrite_rules', 'nextpage_rewrite_rules' );

	// Add filters & actions
	add_filter( 'query_vars', 'nextpage_query_vars' );
	add_action( 'wp', 'nextpage_wp' );
	add_action( 'wp_head', 'nextpage_wp_head', 9 );
}

/**
 * Functions running during wp_head.
 */
function nextpage_wp_head() {

	// Return prev & next link rel
	nextpage_prev_rel_link(); 
	nextpage_next_rel_link(); 
	return;
}

/**
 * Functions that returns prev link rel if prevpage exist.
 */
function nextpage_prev_rel_link() {
	global $paget;

	$prevpagelink = $paget['prevpage']['link'];
	if ( '' != $prevpagelink ) echo "<link rel=\"prev\" href=\"$prevpagelink\" />\n";
	return;
}

/**
 * Functions that returns next link rel if nextpage exist.
 */
function nextpage_next_rel_link() {
	global $paget;
	
	$nextpagelink = $paget['nextpage']['link'];
	if ( '' != $nextpagelink ) echo "<link rel=\"next\" href=\"$nextpagelink\" />\n";
	return;
}

/**
 * Functions that returns 404 page.
 */
function nextpage_return_404() {
	global $wp_query;

	$wp_query->is_404 = true;
	return false;
}

/**
 * Functions running during get_header.
 */
function nextpage_wp() {
	global $post, $paget;

	if ($post) {
		$id = (int) $post->ID;
		$content = $post->post_content;
	}

	if ( is_single() && $post->post_type == 'post' ) {
		
		$paget = get_nextpage_count();
		
		// This happen when 404
		if ( $paget ) {
		
			$pagereq = (get_query_var('paget')) ? get_query_var('paget') : '';
			$pages = get_nextpage_shortcodes();
			$paget['nextpage'] = get_nextpage_next();
			$paget['prevpage'] = get_nextpage_prev();

			if ( $pages && is_array( $paget ) ) {

				$pagenum = $paget['current'];
				$numpages = $paget['count'];
				$index = get_nextpage_summary();
				$pagelinks = get_nextpage_pagelinks();
							
				if ( is_numeric( $pagenum ) ) $post->post_content = $index . $pages[$pagenum]['content'] . $pagelinks;
				if ( $pagenum > 0 ) $post->post_title = $post->post_title . ": " . $pages[$pagenum]['title'];
			}
		} else {
			// Variable title not found, so show 404
			add_action( 'template_redirect', 'nextpage_return_404' );
		}
	}
	return false;
}

/**
 * Function to show the post summary.
 */
function get_nextpage_summary() {

	global $post;

	$pages = get_nextpage_shortcodes();
	$index = '';

	// Before the start of the loop
	$index .= '<ul id="sgrnpt-post-' . $post->ID . '" class="sgrnpt-table">';
	
	foreach ($pages as $page) {
		$index .= '<li><a href="' . $page['link'] . '">' . $page['title'] . '</a></li>';		
	}
	
	// After the end of the loop
	$index .= '</ul>';
	
	return $index;
}

/**
 * Function to show bottom pagelinks.
 */
function get_nextpage_pagelinks() {
	
	$pageprev = get_nextpage_prev();
	$pagenext = get_nextpage_next();
	$pagelinknext = $pagenext['link'];
	$pagelinkprev = $pageprev['link'];
	$pagetitlenext = $pagenext['title'];
	$pagetitleprev = $pageprev['title'];
	$pagelinks = '';

	$pagelinks .= '<div class="paget-link">';
	if ( $pageprev ) $pagelinks .= '<a href="' . $pagelinkprev . '" class="linkprev">&laquo; ' . $pagetitleprev . '</a>';
	if ( $pagenext ) $pagelinks .= '<a href="' . $pagelinknext . '" class="linknext">' . $pagetitlenext . ' &raquo;</a>';
	$pagelinks .= '</div>';

	return $pagelinks;
}

/**
 * Function returning a 2 values array => 'count' = page count, 'current' = current page number
 */
function get_nextpage_count() {
	
	$pagereq = (get_query_var('paget')) ? get_query_var('paget') : '';
	$pages = get_nextpage_shortcodes();
	$count = count($pages);
	
	if ( !$pagereq ) {
		$numbers = array( 'count' => $count, 'current' => 0 );
		return $numbers;
	} else {
		foreach ($pages as $page) {
			if ( sanitize_title($page['title']) == $pagereq ) return array( 'count' => $count, 'current' => $page['number'] );
		}
	}
	return;
}

/**
 * Check for nextpage shortcodes.
 */
function get_nextpage_shortcodes() {
	global $post;

	$content = $post->post_content;
	
	// Variables declaration
	$pattern = "/\[nextpage[^\]]*\]/";
	$pages = array();
	$p = 0;
	
	// Get first part of the post
	$next = get_nextpage_next_shortcode($content);
	$pos = strpos($content, $next);
	
	// Se il post non inizia con uno shortcode aggiungo il sommario alle pagine
	if ( $pos > 0 ) {
		$page['number'] = 0;
		$page['title'] = __( 'Summary', 'sgr-nextpage-titles' );
		$page['content'] = substr($content, 0, $pos);
		$page['link'] = get_permalink($post->ID);
		$pages[] = $page;
	}
	
	preg_match_all($pattern, $content, $matches);
	foreach ($matches[0] as $match) {
		$p++;
		$title = get_nextpage_title($match, $p);
		$page['number'] = $p;
		$page['title'] = $title;
		$page['link'] = get_pagetitle_link($title);
		$page['content'] = get_nextpage_content($match, $content, $p);
		$pages[] = $page;
	}
	return $pages;	
}

/**
 * Check for the next nextpage shortcode.
 */
function get_nextpage_next_shortcode($post, $pos = 0) {

	// Variables declaration
	$pattern = "/\[nextpage[^\]]*\]/";
	$p = 0;
	
	preg_match($pattern, $post, $matches);
	if ( array_key_exists( 0, $matches ) ) {
		return $matches[0];
	}
	return false;
}

/**
 * Return an array with previous page link and title, return false if no previous page.
 */
function get_nextpage_prev() {
	global $paget;

	if ( $paget['current'] > 0 ) {
		$prevpagenum = $paget['current'] -1;
	} else {
		return false;
	}
	$pages = get_nextpage_shortcodes();
	$prevpage = array(
		'title' => $pages[$prevpagenum]['title'],
		'link' => $pages[$prevpagenum]['link']
	);
	return $prevpage;
}

/**
 * Return an array with next page link and title, return false if no next page.
 */
function get_nextpage_next() {
	global $paget;

	if ( $paget['count'] > 0 && $paget['current'] != $paget['count'] -1 ) {
		$nextpagenum = $paget['current'] +1;
	} else {
		return false;
	}
	$pages = get_nextpage_shortcodes();
	$nextpage = array(
		'title' => $pages[$nextpagenum]['title'],
		'link' => $pages[$nextpagenum]['link']
	);
	return $nextpage;
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
	$next = get_nextpage_next_shortcode($part);
	
	// Look for the next shortcode
	if ($next) {
	
		// Return the part before the next shortcode
		$pos = strpos($part, $next);
		$part = substr($part, 0, $pos);
	}
	return $part;
}

/**
 * Register the variables that will be used as parameters on the url.
 */
function nextpage_query_vars($public_query_vars) {

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

/**
 * Create nextpage rewrite rules.
 */
function nextpage_rewrite_rules() {
	global $wp_rewrite;

	// Define custom rewrite tokens
	$rewrite_tag = '%paget%';

	// Add the rewrite tokens
	add_rewrite_tag( $rewrite_tag, '(.+?)', 'paget=' );

	// Define the custom permalink structure
	$rewrite_keywords_structure = $wp_rewrite->root . get_option('permalink_structure') . $rewrite_tag . "/?";

	// Generate the rewrite rules
	$new_rule = $wp_rewrite->generate_rewrite_rules( $rewrite_keywords_structure, false );

	// Add the new rewrite rule into the global rules array
	$wp_rewrite->rules = $new_rule + $wp_rewrite->rules;

	return $wp_rewrite->rules;
}
