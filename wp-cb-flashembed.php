<?php
/*
Plugin Name: WPCB flashembed plugin 
Plugin URI: http://www.homewood.hstd.org/
Description: WP plugin to enable embeding flash with CB
Version: 0.1
Author: Alexander Yachmenev
Author URI: http://www.odesk.com/users/~~94ca72c849152a57
*/
if ( !class_exists( 'wp_cb_flashembed' ) ) {
	class wp_cb_flashembed {
		/**
		 * Initializes plugin variables and sets up wordpress hooks/actions.
		 *
		 * @return void
		 */
		function __construct( ) {
			$this->pluginDir		= basename(dirname(__FILE__));
			$this->pluginPath		= WP_PLUGIN_DIR . '/' . $this->pluginDir;
			add_action('cfct-modules-loaded',  array(&$this, 'wp_cb_flashembed_load'));	
		}

		function wp_cb_flashembed_load() {
			require_once($this->pluginPath . "/flashembed.php");				
		}			
		
	}
	$wp_cb_flashembed = new wp_cb_flashembed();	
}
