<?php

/*
Plugin Name: SGR Nextpage Titles
Plugin URI: http://www.gonk.it/
Description: A plugin that replaces (but not disables) the <code>&lt;!--nextpage--&gt;</code> code and gives the chance to have subtitles for your post subpages. You will have also an index, reporting all subpages, and pretty urls. 
Author: Sergio De Falco aka SGr33n
Version: 0.22
Author URI: http://www.gonk.it/
*/

/* Declarations
| --------------------------------------------- */

define('SGRNPT_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('SGRNPT_PLUGIN_DIR', dirname( __FILE__ ) . '/');
define('SGRNPT_PLUGIN_MAINFILE', __FILE__);
define('SGRNPT_CSS_FOLDER', 'css/');	

/* Includes
| --------------------------------------------- */

require_once(SGRNPT_PLUGIN_DIR . 'functions.php');

/* Hooks and Actions
| --------------------------------------------- */

add_filter('the_content', 'sgrnextpage_content');
add_filter('the_title', 'sgrnextpage_title');
add_filter('query_vars', 'sgrnextpage_query_vars');
add_action('generate_rewrite_rules', 'sgrnextpage_rewrite_rules' );
add_action('init', 'sgrnextpage_plugin_init');
