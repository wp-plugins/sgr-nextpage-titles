<?php

/*
Plugin Name: SGR Nextpage Titles
Plugin URI: http://www.gonk.it/
Description: A plugin that replaces (but not disables) the <code>&lt;!--nextpage--&gt;</code> code and gives the chance to have subtitles for your post subpages. You will have also an index, reporting all subpages, and pretty urls. 
Author: Sergio De Falco aka SGr33n
Version: 0.38
Author URI: http://www.gonk.it/
*/

/* Declarations
| --------------------------------------------- */

define( 'SGRNPT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SGRNPT_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
define( 'SGRNPT_PLUGIN_MAINFILE', __FILE__ );
define( 'SGRNPT_LANG_FOLDER', 'languages/' );	
define( 'SGRNPT_CSS_FOLDER', 'css/' );	

/* Translations
| --------------------------------------------- */

load_plugin_textdomain( 'sgr-nextpage-titles', false, 'sgr-nextpage-titles/' . SGRNPT_LANG_FOLDER );

/* Includes
| --------------------------------------------- */

require_once( SGRNPT_PLUGIN_DIR . 'functions.php' );

/* Hooks and Actions
| --------------------------------------------- */

register_activation_hook(__FILE__, 'sgrntp_activate');
register_deactivation_hook( __FILE__, 'sgrntp_deactivate');
add_action( 'init', 'sgrnpt_plugin_init' );
