<?php

/**
 * Functions running during the plugin intialization.
 */
function sgrnextpage_plugin_init() {

	global $pagenow;

	// Carico gli stili del plugin
	if ( !is_admin() && 'wp-login.php' != $pagenow ) {
		wp_enqueue_style('sgrnpt', SGRNPT_PLUGIN_URL . SGRNPT_CSS_FOLDER . 'default.css');
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
	$nexttitle = "";
	$prevtitle = "";
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
			$page['previous_title'] = $prevtitle;
			$pages[] = $page;
			$prevtitle = $title;
		}
		return $pages;	
	}
}

/**
 * Register the variables that will be used as parameters on the url.
 */
function sgrnextpage_query_vars($public_query_vars) {
	$public_query_vars[] = 'paget';
    return $public_query_vars;
}

/**
 * Manage nextpage shortcodes in the post.
 */
function sgrnextpage_content($content) {
	
	global $post, $wp_rewrite;
	
	if ( is_single() ) {
		
		$paget = (get_query_var('paget')) ? get_query_var('paget') : '';

		$id = (int) $post->ID;
		$content = $post->post_content;
		
		$pages = get_nextpage_shortcode($content);
		$numpages = count($pages);
		$index = "";
		$c = 0;
		
		if ($numpages) {

			// Look for the first shortcode and save it
			$next = get_nextpage_shortcode($content, true);
			if ($next) $content = substr($content, 0, strpos($content, $next));	
		
			$index .= '<ul id="sgrnpt-post-' . $post->ID . '" class="sgrnpt-table">';
			foreach ($pages as $page) {
				$pagelink = get_pagetitle_link($page['title']);
				$index .= '<li><a href="' . $pagelink . '">' . $page['title'] . '</a></li>';
				//$index .= '<li>' . $page['title'] . '</li>';
				//$index .= '<li>' . get_pagetitle_link($page['title']) . '</li>';
				if ( sanitize_title($page['title'] ) == $paget) $content = $page['content'];
			}
			$index .= '</ul>';
		}
		return $index . $content;
	} else {

		// Look for the first shortcode and save it
		$next = get_nextpage_shortcode($content, true);
		if ($next) $content = substr($content, 0, strpos($content, $next));	
		
		return $content;
	}
}

/**
 * Manage the post title.
 */
function sgrnextpage_title($title) {
	return $title;
}

/**
 * Retrieve links for subpages.
 */
function get_pagetitle_link($pagetitle, $escape = true ) {
	global $wp_rewrite, $post;

	$request = remove_query_arg( 'paget' );

	if ( !$wp_rewrite->using_permalinks() || is_admin() ) {
		
		$base = trailingslashit( get_bloginfo( 'url' ) );
		
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
function sgrnextpage_rewrite_rules() {
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

/**
 * A function to flush rewrite rules.
 */
function flush_rules() {
	global $wp_rewrite;

	$wp_rewrite->flush_rules();

	return true;
}
