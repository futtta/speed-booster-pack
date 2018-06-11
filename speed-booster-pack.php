<?php
/**
 * Plugin Name: Speed Booster Pack
 * Plugin URI: http://wordpress.org/plugins/speed-booster-pack/
 * Description: Speed Booster Pack allows you to improve your page loading speed and get a higher score on the major
 * speed testing services such as <a href="http://gtmetrix.com/">GTmetrix</a>, <a
 * href="http://developers.google.com/speed/pagespeed/insights/">Google PageSpeed</a> or other speed testing tools.
 * Version: 3.7.2
 * Author: Macho Themes
 * Author URI: https://www.machothemes.com/
 * License: GPLv2
 */

/*  Copyright 2018 Macho Themes (email : support [at] machothemes [dot] com)

	THIS PROGRAM IS FREE SOFTWARE; YOU CAN REDISTRIBUTE IT AND/OR MODIFY
	IT UNDER THE TERMS OF THE GNU GENERAL PUBLIC LICENSE AS PUBLISHED BY
	THE FREE SOFTWARE FOUNDATION; EITHER VERSION 2 OF THE LICENSE, OR
	(AT YOUR OPTION) ANY LATER VERSION.

	THIS PROGRAM IS DISTRIBUTED IN THE HOPE THAT IT WILL BE USEFUL,
	BUT WITHOUT ANY WARRANTY; WITHOUT EVEN THE IMPLIED WARRANTY OF
	MERCHANTABILITY OR FITNESS FOR A PARTICULAR PURPOSE.  SEE THE
	GNU GENERAL PUBLIC LICENSE FOR MORE DETAILS.

	YOU SHOULD HAVE RECEIVED A COPY OF THE GNU GENERAL PUBLIC LICENSE
	ALONG WITH THIS PROGRAM; IF NOT, WRITE TO THE FREE SOFTWARE
	FOUNDATION, INC., 51 FRANKLIN ST, FIFTH FLOOR, BOSTON, MA  02110-1301  USA
*/


//@todo: rework the CSS Async functionality
//@todo: fix lazyLoad with WooCommerce <- it actually works flawlessly, it doesn't seem to work with AO and/or Cloudflare hosted CSS
//@todo: add system info menu page
//@todo: automatically collapse accordeons on "advanced" tab
/**
 *
 * register_setting( 'speed_booster_settings_group', 'sbp_js_footer_exceptions1' );
 * register_setting( 'speed_booster_settings_group', 'sbp_js_footer_exceptions2' );
 * register_setting( 'speed_booster_settings_group', 'sbp_js_footer_exceptions3' );
 * register_setting( 'speed_booster_settings_group', 'sbp_js_footer_exceptions4' );
 *
 * register_setting( 'speed_booster_settings_group', 'sbp_defer_exceptions1' );
 * register_setting( 'speed_booster_settings_group', 'sbp_defer_exceptions2' );
 * register_setting( 'speed_booster_settings_group', 'sbp_defer_exceptions3' );
 * register_setting( 'speed_booster_settings_group', 'sbp_defer_exceptions4' );
 * register_setting( 'speed_booster_settings_group', 'sbp_integer' );
 */

/*----------------------------------------------------------------------------------------------------------
	Global Variables
-----------------------------------------------------------------------------------------------------------*/

/**
 * Default plugin values
 *
 * @since 3.7
 */
$sbp_defaults = array(
	'remove_emojis'    => 1, // remove emoji scripts
	'remove_wsl'       => 1, // remove WSL link in header
	'remove_adjacent'  => 1, // remove post adjacent links
	'wml_link'         => 1, // remove Windows Manifest Live link
	'rsd_link'         => 1, // remove really simple discovery
	'wp_generator'     => 1, // remove WP version
	'remove_all_feeds' => 1, // remove all WP feeds
	'disable_xmlrpc'   => 1, // disable XML-RPC pingbacks
	'font_awesome'     => 1, // remove extra font awesome styles
	'query_strings'    => 1, // remove query strings
);

$sbp_options = get_option( 'sbp_settings', (array) $sbp_defaults );    // retrieve the plugin settings from the options table

/*----------------------------------------------------------------------------------------------------------
	Define some useful plugin constants
-----------------------------------------------------------------------------------------------------------*/

define( 'SPEED_BOOSTER_PACK_PATH', plugin_dir_path( __FILE__ ) );                    // Defining plugin dir path
define( 'SPEED_BOOSTER_PACK_VERSION', '3.7' );                                       // Defining plugin version
define( 'SBP_FOOTER', 10 );                                                          // Defining css position
define( 'SBP_FOOTER_LAST', 99999 );                                                  // Defining css last position
if ( ! defined( 'SHORTPIXEL_AFFILIATE_CODE' ) ) {
	define( 'SHORTPIXEL_AFFILIATE_CODE', 'U3NQVWK31472' );
}


define( 'APHMS_PLUGIN_DIR_NAME', 'speed-booster-pack' );
define( 'APHMS_PLUGIN_FILE_NAME', 'speed-booster-pack.php' );
define( 'APHMS_DS', DIRECTORY_SEPARATOR );
define( 'APHMS_PLUGIN_PATH', WP_PLUGIN_DIR . APHMS_DS . APHMS_PLUGIN_DIR_NAME );
define( 'APHMS_PLUGIN_URL', WP_PLUGIN_URL . '/' . APHMS_PLUGIN_DIR_NAME );

/*----------------------------------------------------------------------------------------------------------
	Main Plugin Class
-----------------------------------------------------------------------------------------------------------*/

if ( ! class_exists( 'Speed_Booster_Pack' ) ) {

	class Speed_Booster_Pack {

		private $to_do = array();


		/*----------------------------------------------------------------------------------------------------------
			Function Construct
		-----------------------------------------------------------------------------------------------------------*/

		public function __construct() {
			global $sbp_options;

			// Enqueue admin scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'sbp_admin_enqueue_scripts' ) );

			// load plugin textdomain
			add_action( 'plugins_loaded', array( $this, 'sbp_load_translation' ) );

			// Load plugin settings page
			require_once( SPEED_BOOSTER_PACK_PATH . 'inc/settings.php' );
			new Speed_Booster_Pack_Options();

			// Load main plugin functions
			require_once( SPEED_BOOSTER_PACK_PATH . 'inc/core.php' );
			new Speed_Booster_Pack_Core();

			// Enqueue admin style
			add_action( 'admin_enqueue_scripts', array( $this, 'sbp_enqueue_styles' ) );

			// Filters
			$this->path = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$this->path", array( $this, 'sbp_settings_link' ) );

			// load the uninstall feedback class
			require_once 'feedback/class-epsilon-feedback.php';
			new Epsilon_Feedback( __FILE__ );

			require_once SPEED_BOOSTER_PACK_PATH . 'inc/minifiers/CommentPreserver.php';
			require_once SPEED_BOOSTER_PACK_PATH . 'inc/minifiers/HTML/Main.php';
			require_once SPEED_BOOSTER_PACK_PATH . 'inc/minifiers/CSS/Main.php';
			require_once SPEED_BOOSTER_PACK_PATH . 'inc/minifiers/CSS/Compressor.php';
			require_once SPEED_BOOSTER_PACK_PATH . 'inc/minifiers/CSS/UriRewriter.php';
			require_once SPEED_BOOSTER_PACK_PATH . 'inc/minifiers/JS/Main.php';


			//add_action( 'get_header', array( $this, 'start_minify' ), -1 );

			//add_action( 'wp_print_scripts', array( $this, 'merge_styles' ), 10 );

		}    // END public function __construct


		public function merge_styles() {

			/*
			if ( ! $this->options['merge_styles'] ) {
				return false;
			}
			*/

			global $wp_styles;

			$token        = time();
			$merged_style = '';
			$list_handles = array();


			$queues = $wp_styles->queue;
			$wp_styles->all_deps( $queues );
			$this->to_do['style'] = $wp_styles->queue;



			$path = APHMS_PLUGIN_PATH . APHMS_DS . 'merged' . APHMS_DS . 'merged-style' . '-' . $token . '.css';

			foreach ( $wp_styles->to_do as $handle ) {

				if ( ! key_exists( $handle, $wp_styles->registered ) ) {
					continue;
				}

				$file_path     = $this->file_path( $handle, $wp_styles->registered[ $handle ]->src, 'style' );
				$file_contents = file_get_contents( $file_path ); // @todo: should use WP FileSystem API with fallback to file_get_contents here

				/**
				 * We have to save the handle outside the file_exists to make sure all handle are saved,
				 * this is useful when checking cache
				 */
				$list_handles[]         = $handle;
				$log_handles[ $handle ] = $wp_styles->registered[ $handle ]->src;


				$this->deregister['style'][] = $handle;
				$minifier                    = Minify_CSS::minify( $file_contents );

				$merged_style .= $minifier;

			}

			if ( $merged_style ) {
				file_put_contents( $path, '/* SPB Merge Scripts v' . SPEED_BOOSTER_PACK_VERSION . ' */' . "\r\n" . $merged_style );

				$log_style_handles = '';
				foreach ( $log_handles as $handle => $url ) {
					$log_style_handles .= $handle . ': ' . $url . "\r\n";
				}
				$path_list_handle = APHMS_PLUGIN_PATH . APHMS_DS . 'merged' . APHMS_DS . 'style-handles.txt';
				file_put_contents( $path_list_handle, $log_style_handles );

			}

			foreach ( $this->deregister['style'] as $handle ) {

				wp_deregister_style( $handle );
			}

			$qstring      = '?rand=' . time();
			$file_css_url = APHMS_PLUGIN_URL . '/merged/' . 'merged-style' . '-' . $token . '.css' . $qstring;
			wp_enqueue_style( 'merged-style', $file_css_url, '', SPEED_BOOSTER_PACK_VERSION );

		}


		/**
		 * File path
		 * Find relative path of each style or script
		 */
		private function file_path( $handle, $src, $type ) {
			$clean_hash = $clean = strtok( $src, '?' );

			$site_url       = site_url();
			$parse_site_url = parse_url( $site_url );
			$parse_url      = parse_url( $clean_hash );

			$site_path = '';
			if ( key_exists( 'path', $parse_site_url ) ) {
				$site_path = $parse_site_url['path'];
			}

			if ( key_exists( 'host', $parse_url ) ) {


				$file_path = str_replace( $site_path, '', $parse_url['path'] );
				$file_path = ltrim( $file_path, '/' );
			} else {
				$file_path = ltrim( $parse_url['path'], '/' );
			}

			return $file_path;
		}

		public function start_minify() {

			if ( ! is_admin() ) {
				ob_start( array( 'Speed_Booster_Pack', 'spb_html_compression' ) );
			}
		}


		function spb_html_compression( $buffer ) {


			$initial = strlen( $buffer );

			$buffer = Minify_HTML::minify( $buffer, array(
				'jsMinifier'  => array( 'JSMin', 'minify' ),
				'cssMinifier' => array( 'Minify_CSS', 'minify' ),
			) );


			$final   = strlen( $buffer );
			$savings = round( ( ( $initial - $final ) / $initial * 100 ), 4 );

			$show_compression_values = apply_filters( 'spb_show_compression', true );

			if ( $show_compression_values ) {
				if ( 0 !== $savings ) {
					$buffer .= PHP_EOL . '<!--' . PHP_EOL . '*** This site runs Speed Booster Plugin - http://wordpress.org/plugins/speed-booster-pack ***' . PHP_EOL . '*** Total size saved: ' . esc_html( $savings ) . '% | Size before compression: ' . esc_html( $this->format_size_units( $initial ) ) . ' | Size after compression: ' . esc_html( $this->format_size_units( $final ) ) . ' . ***' . PHP_EOL . '-->';
				}
			}


			return $buffer;
		}

		public function format_size_units( $bytes ) {
			if ( $bytes >= 1073741824 ) {
				$bytes = number_format( $bytes / 1073741824, 2 ) . ' GB';
			} elseif ( $bytes >= 1048576 ) {
				$bytes = number_format( $bytes / 1048576, 2 ) . ' MB';
			} elseif ( $bytes >= 1024 ) {
				$bytes = number_format( $bytes / 1024, 2 ) . ' KB';
			} elseif ( $bytes > 1 ) {
				$bytes = $bytes . ' bytes';
			} elseif ( 1 == $bytes ) {
				$bytes = $bytes . ' byte';
			} else {
				$bytes = '0 bytes';
			}

			return $bytes;
		}


		/*----------------------------------------------------------------------------------------------------------
			Load plugin textdomain
		-----------------------------------------------------------------------------------------------------------*/

		function sbp_load_translation() {
			load_plugin_textdomain( 'sb-pack', false, SPEED_BOOSTER_PACK_PATH . '/lang/' );
		}


		/*----------------------------------------------------------------------------------------------------------
			Activate the plugin
		-----------------------------------------------------------------------------------------------------------*/

		public static function sbp_activate() { // @todo: look below


			/*
			 * public function hook_page_cache_purge() {
				// hook into a collection of page cache purge actions if filter allows.
				if ( apply_filters( 'autoptimize_filter_main_hookpagecachepurge', true ) ) {
					$page_cache_purge_actions = array(
						'after_rocket_clean_domain', // exists.
						'hyper_cache_purged', // Stefano confirmed this will be added.
						'w3tc_flush_posts', // exits.
						'w3tc_flush_all', // exists.
						'ce_action_cache_cleared', // Sven confirmed this will be added.
						'comet_cache_wipe_cache', // still to be confirmed by Raam.
						'wp_cache_cleared', // cfr. https://github.com/Automattic/wp-super-cache/pull/537.
						'wpfc_delete_cache', // Emre confirmed this will be added this.
						'swift_performance_after_clear_all_cache', // swift perf. yeah!
					);
					$page_cache_purge_actions = apply_filters( 'autoptimize_filter_main_pagecachepurgeactions', $page_cache_purge_actions );
					foreach ( $page_cache_purge_actions as $purge_action ) {
						add_action( $purge_action, 'autoptimizeCache::clearall_actionless' );
					}
				}
			}
			*/

		}


		/*----------------------------------------------------------------------------------------------------------
			Deactivate the plugin
		-----------------------------------------------------------------------------------------------------------*/

		public static function sbp_deactivate() {
		}


		/*----------------------------------------------------------------------------------------------------------
			CSS style of the plugin options page
		-----------------------------------------------------------------------------------------------------------*/

		function sbp_enqueue_styles( $hook ) {

			// load stylesheet only on plugin options page
			global $sbp_settings_page;
			if ( $hook != $sbp_settings_page ) {
				return;
			}
			wp_enqueue_style( 'sbp-styles', plugin_dir_url( __FILE__ ) . 'css/style.css' );
			wp_enqueue_style( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'css/vendors/jquery-ui/jquery-ui.min.css' );

		}    //	End function sbp_enqueue_styles


		/*----------------------------------------------------------------------------------------------------------
			Enqueue admin scripts to plugin options page
		-----------------------------------------------------------------------------------------------------------*/

		public function sbp_admin_enqueue_scripts( $hook_sbp ) {
			// load scripts only on plugin options page
			global $sbp_settings_page;
			if ( $hook_sbp != $sbp_settings_page ) {
				return;
			}
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'postbox' );

			wp_enqueue_script( 'sbp-admin-scripts', plugins_url( 'inc/js/admin-scripts.js', __FILE__ ), array(
				'jquery',
				'postbox',
				'jquery-ui-slider',
			), SPEED_BOOSTER_PACK_VERSION, true );

			wp_enqueue_script( 'sbp-plugin-install', plugins_url( 'inc/js/plugin-install.js', __FILE__ ), array(
				'jquery',
				'updates',
			), SPEED_BOOSTER_PACK_VERSION, true );

		}


		/*----------------------------------------------------------------------------------------------------------
			Add settings link on plugins page
		-----------------------------------------------------------------------------------------------------------*/

		function sbp_settings_link( $links ) {

			$settings_link = ' <a href="admin.php?page=sbp-options">Settings</a > ';
			array_unshift( $links, $settings_link );

			return $links;

		}    //	End function sbp_settings_link


	}//	End class Speed_Booster_Pack
}    //	End if (!class_exists("Speed_Booster_Pack")) (1)

if ( class_exists( 'Speed_Booster_Pack' ) ) {

	// Installation and uninstallation hooks
	register_activation_hook( __FILE__, array( 'Speed_Booster_Pack', 'sbp_activate' ) );
	register_deactivation_hook( __FILE__, array( 'Speed_Booster_Pack', 'sbp_deactivate' ) );

	// instantiate the plugin class
	$speed_booster_pack = new Speed_Booster_Pack();

}    //	End if (!class_exists("Speed_Booster_Pack")) (2)
