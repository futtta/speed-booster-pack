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



/**
 * @todo: add system info menu page
 */

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




/*----------------------------------------------------------------------------------------------------------
	Main Plugin Class
-----------------------------------------------------------------------------------------------------------*/

if ( ! class_exists( 'Speed_Booster_Pack' ) ) {

	class Speed_Booster_Pack {

		protected $sbp_options;

		const INIT_EARLIER_PRIORITY = -1;
		const DEFAULT_HOOK_PRIORITY = 2;


		/*----------------------------------------------------------------------------------------------------------
			Function Construct
		-----------------------------------------------------------------------------------------------------------*/

		public function __construct() {
			$this->sbp_options = get_option( 'sbp_settings', self::plugin_settings_defaults() );

			// Enqueue admin scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'sbp_admin_enqueue_scripts' ) );

			// load plugin textdomain
			add_action( 'plugins_loaded', array( $this, 'load_translation' ) );
			add_action( 'plugins_loaded', array( $this, 'start_setup' ) );
			add_action( 'plugins_loaded', array( $this, 'startup_purge_cache' ) );

			// Load plugin settings page
			require_once( SPEED_BOOSTER_PACK_PATH . 'inc/settings.php' );
			new Speed_Booster_Pack_Options();

			// Load main plugin functions
			require_once( SPEED_BOOSTER_PACK_PATH . 'inc/core.php' );
			new Speed_Booster_Pack_Core();

			// Enqueue admin style
			add_action( 'admin_enqueue_scripts', array( $this, 'sbp_enqueue_styles' ) );

			// Settings Links
			$this->path = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$this->path", array( $this, 'sbp_settings_link' ) );

			// Custom action hooks
			add_action( 'speed_booster_setup_done', array( $this, 'check_cache_then_run' ) );

			// load the uninstall feedback class
			require_once 'feedback/class-epsilon-feedback.php';
			new Epsilon_Feedback( __FILE__ );

		}    // END public function __construct

		/**
		 * Function that holds options table defaults
		 * Pre-populate on plugin install
		 *
		 *
		 * @return void
		 */
		public static function plugin_settings_defaults() {

			return array(
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
		}

		public function start_setup() {

			// Do we gzip in php when caching or is the webserver doing it?
			define( 'SPEED_BOOSTER_CACHE_NOGZIP', (bool) get_option( 'speed_booster_cache_nogzip' ) );

			/**
			 * Override these by specifying them in your wp-config.php file
			 */
			if ( ! defined( 'SPEED_BOOSTER_WP_CONTENT_NAME' ) ) {
				define( 'SPEED_BOOSTER_WP_CONTENT_NAME', '/' . wp_basename( WP_CONTENT_DIR ) );
			}
			if ( ! defined( 'SPEED_BOOSTER_CACHE_URL' ) ) {
				define( 'SPEED_BOOSTER_CACHE_URL', '/cache/speed-booster-pack/' );
			}
			if ( ! defined( 'SPEED_BOOSTER_CACHEFILE_PREFIX' ) ) {
				define( 'SPEED_BOOSTER_CACHEFILE_PREFIX', 'spb_' );
			}
			// Note: trailing slash is not optional!
			if ( ! defined( 'SPEED_BOOSTER_CACHE_DIR' ) ) {
				define( 'SPEED_BOOSTER_CACHE_DIR', self::get_pathname() );
			}

			if ( ! defined( 'WP_ROOT_DIR' ) ) {
				define( 'WP_ROOT_DIR', substr( WP_CONTENT_DIR, 0, strlen( WP_CONTENT_DIR ) - strlen( SPEED_BOOSTER_WP_CONTENT_NAME ) ) );
			}

			if ( ! defined( 'SPEED_BOOSTER_WP_SITE_URL' ) ) {
				if ( function_exists( 'domain_mapping_siteurl' ) ) {
					define( 'SPEED_BOOSTER_WP_SITE_URL', domain_mapping_siteurl( get_current_blog_id() ) );
				} else {
					define( 'SPEED_BOOSTER_WP_SITE_URL', site_url() );
				}
			}
			if ( ! defined( 'SPEED_BOOSTERWP_CONTENT_URL' ) ) {
				if ( function_exists( 'domain_mapping_siteurl' ) ) {
					define( 'SPEED_BOOSTER_WP_CONTENT_URL', str_replace( get_original_url( speed_booster_WP_SITE_URL ), speed_booster_WP_SITE_URL, content_url() ) );
				} else {
					define( 'SPEED_BOOSTER_WP_CONTENT_URL', content_url() );
				}
			}
			if ( ! defined( 'SPEED_BOOSTER_CACHE_URL' ) ) {
				if ( is_multisite() && apply_filters( 'speed_booster_separate_blog_caches', true ) ) {
					$blog_id = get_current_blog_id();
					define( 'SPEED_BOOSTER_CACHE_URL', SPEED_BOOSTER_WP_CONTENT_URL . SPEED_BOOSTER_CACHE_URL . $blog_id . '/' );
				} else {
					define( 'SPEED_BOOSTER_CACHE_URL', SPEED_BOOSTER_WP_CONTENT_URL . SPEED_BOOSTER_CACHE_URL );
				}
			}
			if ( ! defined( 'SPEED_BOOSTER_WP_ROOT_URL' ) ) {
				define( 'SPEED_BOOSTER_WP_ROOT_URL', str_replace( SPEED_BOOSTER_WP_CONTENT_NAME, '', SPEED_BOOSTER_WP_ROOT_URL ) );
			}
			if ( ! defined( 'SPEED_BOOSTER_HASH' ) ) {
				define( 'SPEED_BOOSTER_HASH', wp_hash( SPEED_BOOSTER_CACHE_URL ) );
			}

			do_action( 'speed_booster_setup_done' );
		}

		/**
		 * Hook onto other know page caching systems and clear all cache
		 *
		 * @todo: should implement speed_Cache::clearall_actionless
		 *
		 * @return void
		 */
		public function startup_purge_cache() {
			// hook into a collection of page cache purge actions if filter allows.
			if ( apply_filters( 'speed_booster_filter_main_hookpagecachepurge', true ) ) {
				$page_cache_purge_actions = array(
					'after_rocket_clean_domain',
					'hyper_cache_purged',
					'w3tc_flush_posts',
					'w3tc_flush_all',
					'ce_action_cache_cleared',
					'comet_cache_wipe_cache',
					'wp_cache_cleared',
					'wpfc_delete_cache',
					'swift_performance_after_clear_all_cache', // swift perf!
				);
				$page_cache_purge_actions = apply_filters( 'speed_booster_filter_main_pagecachepurgeactions', $page_cache_purge_actions );
				foreach ( $page_cache_purge_actions as $purge_action ) {
					/**
				 * @todo: fix this
				 */
					//add_action( $purge_action, 'speed_Cache::clearall_actionless' );
				}
			}
		}

		/**
		 * Function that checks if the cache directory is writeable
		 * Display admin notice if it's not
		 *
		 * @return void
		 */
		public function check_cache_then_run() {
			if ( self::cacheavail() ) {

				if ( $this->sbp_options['minify_html_js'] || get_option( 'spb_minify_js' ) || get_option( 'spb_minify_css' ) ) {

					// Hook into WordPress frontend.
					if ( defined( 'SPEED_BOOSTER_INIT_EARLIER' ) ) {
						add_action( 'init', array( $this, 'start_buffering' ), self::INIT_EARLIER_PRIORITY );
					} else {
						if ( ! defined( 'SPEED_BOOSTER_HOOK_INTO' ) ) {
							define( 'SPEED_BOOSTER_HOOK_INTO', 'template_redirect' );
						}
						add_action(
							constant( 'SPEED_BOOSTER_HOOK_INTO' ), array( $this, 'start_buffering' ), self::DEFAULT_HOOK_PRIORITY
						);
					}
				}
			} else {
				add_action( 'admin_notices', self::notice_cache_unavailable() );
			}
		}

		/**
	 * Setup output buffering if needed.
	 *
	 * @return void
	 */
		public function start_buffering() {

			if ( $this->should_buffer() ) {

				if ( $this->sbp_options['minify_html_js'] ) {
					if ( ! defined( 'CONCATENATE_SCRIPTS' ) ) {
						define( 'CONCATENATE_SCRIPTS', false );
					}
					if ( ! defined( 'COMPRESS_SCRIPTS' ) ) {
						define( 'COMPRESS_SCRIPTS', false );
					}
				}

				/*
				if ( $conf->get( 'speed_booster_js' ) ) {
					if ( ! defined( 'CONCATENATE_SCRIPTS' ) ) {
						define( 'CONCATENATE_SCRIPTS', false );
					}
					if ( ! defined( 'COMPRESS_SCRIPTS' ) ) {
						define( 'COMPRESS_SCRIPTS', false );
					}
				}


				if ( $conf->get( 'speed_booster_css' ) ) {
					if ( ! defined( 'COMPRESS_CSS' ) ) {
						define( 'COMPRESS_CSS', false );
					}
				}
				*/

				if ( apply_filters( 'speed_booster_filter_obkiller', false ) ) {
					while ( ob_get_level() > 0 ) {
						ob_end_clean();
					}
				}

				// Now, start the real thing!
				ob_start( array( $this, 'end_buffering' ) );
			}
		}

		/**
	 * Returns true if all the conditions to start output buffering are satisfied.
	 *
	 * @param bool $doing_tests Allows overriding the optimization of only
	 *                          deciding once per request (for use in tests).
	 *
	 * @return bool
	 */
		public function should_buffer( $doing_tests = false ) {
			static $do_buffering = null;

			// Only check once in case we're called multiple times by others but
			// still allows multiple calls when doing tests.
			if ( null === $do_buffering || $doing_tests ) {

				$sbp_noptimize = false;

				// Checking for DONOTMINIFY constant as used by e.g. WooCommerce POS.
				if ( defined( 'DONOTMINIFY' ) && ( constant( 'DONOTMINIFY' ) === true || constant( 'DONOTMINIFY' ) === 'true' ) ) {
					$sbp_noptimize = true;
				}

				// Skip checking query strings if they're disabled.
				if ( apply_filters( 'speed_booster_filter_honor_qs_noptimize', true ) ) {
					// Check for `ao_noptimize` (and other) keys in the query string
					// to get non-optimized page for debugging.
					$keys = array(
						'ao_noptimize',
						'ao_noptirocket',
						'spb_noptimize',
					);
					foreach ( $keys as $key ) {
						if ( array_key_exists( $key, $_GET ) && '1' === $_GET[ $key ] ) {
							$sbp_noptimize = true;
							break;
						}
					}
				}

				// If setting says not to optimize logged in user and user is logged in...
				if ( 'on' !== get_option( 'speed_booster_optimize_logged', 'on' ) && is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
					$sbp_noptimize = true;
				}

				// If setting says not to optimize cart/checkout.
				if ( 'on' !== get_option( 'speed_booster_optimize_checkout', 'on' ) ) {
					// Checking for woocommerce, easy digital downloads and wp ecommerce...
					foreach ( array(
						'is_checkout',
						'is_cart',
						'edd_is_checkout',
						'wpsc_is_cart',
						'wpsc_is_checkout',
					) as $func ) {
						if ( function_exists( $func ) && $func() ) {
							$sbp_noptimize = true;
							break;
						}
					}
				}

				// Allows blocking of autoptimization on your own terms regardless of above decisions.
				$sbp_noptimize = (bool) apply_filters( 'speed_booster_filter_noptimize', $sbp_noptimize );

				// Check for site being previewed in the Customizer (available since WP 4.0).
				$is_customize_preview = false;
				if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
					$is_customize_preview = is_customize_preview();
				}

				/**
			 * We only buffer the frontend requests (and then only if not a feed
			 * and not turned off explicitly and not when being previewed in Customizer)!
			 * NOTE: Tests throw a notice here due to is_feed() being called
			 * while the main query hasn't been ran yet. Thats why we use
			 * speed_booster_INIT_EARLIER in tests.
			 */
				$do_buffering = ( ! is_admin() && ! is_feed() && ! $sbp_noptimize && ! $is_customize_preview );
			}

			return $do_buffering;
		}

		/**
	 * Returns true if given markup is considered valid/processable/optimizable.
	 *
	 * @param string $content Markup.
	 *
	 * @return bool
	 */
		public function is_valid_buffer( $content ) {
			// Defaults to true.
			$valid = true;

			$has_no_html_tag    = ( false === stripos( $content, '<html' ) );
			$has_xsl_stylesheet = ( false !== stripos( $content, '<xsl:stylesheet' ) );
			$has_html5_doctype  = ( preg_match( '/^<!DOCTYPE.+html>/i', $content ) > 0 );

			if ( $has_no_html_tag ) {
				// Can't be valid amp markup without an html tag preceding it.
				$is_amp_markup = false;
			} else {
				$is_amp_markup = self::is_amp_markup( $content );
			}

			// If it's not html, or if it's amp or contains xsl stylesheets we don't touch it.
			if ( $has_no_html_tag && ! $has_html5_doctype || $is_amp_markup || $has_xsl_stylesheet ) {
				$valid = false;
			}

			return $valid;
		}

		/**
	 * Returns true if given $content is considered to be AMP markup.
	 * This is far from actual validation against AMP spec, but it'll do for now.
	 *
	 * @param string $content Markup to check.
	 *
	 * @return bool
	 */
		public static function is_amp_markup( $content ) {
			$is_amp_markup = preg_match( '/<html[^>]*(?:amp|⚡)/i', $content );

			return (bool) $is_amp_markup;
		}

		/**
	 * Processes/optimizes the output-buffered content and returns it.
	 * If the content is not processable, it is returned unmodified.
	 *
	 * @param string $content Buffered content.
	 *
	 * @return string
	 */
		public function end_buffering( $content ) {

			// Bail early without modifying anything if we can't handle the content.
			if ( ! $this->is_valid_buffer( $content ) ) {
				return $content;
			}

			return $content;
		}


		public static function notice_cache_unavailable() {
			echo '<div class="error"><p>';
			// Translators: %s is the cache directory location.
			printf( __( 'Speed Booster Pack can\'t write to the cache directory (%s), please fix to enable CSS/JS optimization to work properly!', 'sb-pack' ), SPEED_BOOSTER_CACHE_DIR );
			echo '</p></div>';
		}




		/*----------------------------------------------------------------------------------------------------------
			Load plugin textdomain
		-----------------------------------------------------------------------------------------------------------*/

		function load_translation() {
			load_plugin_textdomain( 'sb-pack', false, SPEED_BOOSTER_PACK_PATH . '/lang/' );
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

			wp_enqueue_script(
				'sbp-admin-scripts', plugins_url( 'inc/js/admin-scripts.js', __FILE__ ), array(
					'jquery',
					'postbox',
					'jquery-ui-slider',
				), SPEED_BOOSTER_PACK_VERSION, true
			);

			wp_enqueue_script(
				'sbp-plugin-install', plugins_url( 'inc/js/plugin-install.js', __FILE__ ), array(
					'jquery',
					'updates',
				), SPEED_BOOSTER_PACK_VERSION, true
			);

		}


		/*----------------------------------------------------------------------------------------------------------
			Add settings link on plugins page
		-----------------------------------------------------------------------------------------------------------*/

		function sbp_settings_link( $links ) {

			$settings_link = ' <a href="admin.php?page=sbp-options">Settings</a > ';
			array_unshift( $links, $settings_link );

			return $links;

		}    //	End function sbp_settings_link

		/**
	 * Returns the cache directory pathname used.
	 * Done as a function so we canSlightly different
	 * if multisite is used and `speed_booster_separate_blog_caches` filter
	 * is used.
	 *
	 * @return string
	 */
		public static function get_pathname() {
			$pathname = self::get_pathname_base();

			if ( is_multisite() && apply_filters( 'speed_booster_separate_blog_caches', true ) ) {
				$blog_id   = get_current_blog_id();
				$pathname .= $blog_id . '/';
			}

			return $pathname;
		}

		/**
	 * Returns the base path of our cache directory.
	 *
	 * @return string
	 */
		protected static function get_pathname_base() {
			$pathname = WP_CONTENT_DIR . SPEED_BOOSTER_CACHE_URL;

			return $pathname;
		}

		/**
	 * Ensures the cache directory exists, is writeable and contains the
	 * required .htaccess files.
	 * Returns false in case it fails to ensure any of those things.
	 *
	 * @return bool
	 */
		public static function cacheavail() {
			if ( ! defined( 'speed_booster_CACHE_DIR' ) ) {
				// We didn't set a cache.
				return false;
			}

			foreach ( array( '', 'js', 'css' ) as $dir ) {
				if ( ! self::check_cache_dir( speed_booster_CACHE_DIR . $dir ) ) {
					return false;
				}
			}

			// Using .htaccess inside our cache folder to overrule wp-super-cache.
			$htaccess = speed_booster_CACHE_DIR . '/.htaccess';
			if ( ! is_file( $htaccess ) ) {
				/**
			 * Create `wp-content/AO_htaccess_tmpl` file with
			 * whatever htaccess rules you might need
			 * if you want to override default AO htaccess
			 */
				$htaccess_tmpl = WP_CONTENT_DIR . '/AO_htaccess_tmpl';
				if ( is_file( $htaccess_tmpl ) ) {
					$content = file_get_contents( $htaccess_tmpl );
				} elseif ( is_multisite() || ! speed_booster_CACHE_NOGZIP ) {
					$content = '<IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType text/css A30672000
        ExpiresByType text/javascript A30672000
        ExpiresByType application/javascript A30672000
</IfModule>
<IfModule mod_headers.c>
    Header append Cache-Control "public, immutable"
</IfModule>
<IfModule mod_deflate.c>
        <FilesMatch "\.(js|css)$">
        SetOutputFilter DEFLATE
    </FilesMatch>
</IfModule>
<IfModule mod_authz_core.c>
    <Files *.php>
        Require all granted
    </Files>
</IfModule>
<IfModule !mod_authz_core.c>
    <Files *.php>
        Order allow,deny
        Allow from all
    </Files>
</IfModule>';
				} else {
					$content = '<IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType text/css A30672000
        ExpiresByType text/javascript A30672000
        ExpiresByType application/javascript A30672000
</IfModule>
<IfModule mod_headers.c>
    Header append Cache-Control "public, immutable"
</IfModule>
<IfModule mod_deflate.c>
    <FilesMatch "\.(js|css)$">
        SetOutputFilter DEFLATE
    </FilesMatch>
</IfModule>
<IfModule mod_authz_core.c>
    <Files *.php>
        Require all denied
    </Files>
</IfModule>
<IfModule !mod_authz_core.c>
    <Files *.php>
        Order deny,allow
        Deny from all
    </Files>
</IfModule>';
				}
				@file_put_contents( $htaccess, $content ); // @codingStandardsIgnoreLine
			}

			// All OK!
			return true;
		}

		/**
	 * Ensures the specified `$dir` exists and is writeable.
	 * Returns false if that's not the case.
	 *
	 * @param string $dir Directory to check/create.
	 *
	 * @return bool
	 */
		protected static function check_cache_dir( $dir ) {
			// Try creating the dir if it doesn't exist.
			if ( ! file_exists( $dir ) ) {
				@mkdir( $dir, 0775, true ); // @codingStandardsIgnoreLine
				if ( ! file_exists( $dir ) ) {
					return false;
				}
			}

			// If we still cannot write, bail.
			if ( ! is_writable( $dir ) ) {
				return false;
			}

			// Create an index.html in there to avoid prying eyes!
			$idx_file = rtrim( $dir, '/\\' ) . '/index.html';
			if ( ! is_file( $idx_file ) ) {
				@file_put_contents( $idx_file, '<html><head><meta name="robots" content="noindex, nofollow"></head><body>Generated by <a href="http://wordpress.org/extend/plugins/speed_/" rel="nofollow">speed_</a></body></html>' ); // @codingStandardsIgnoreLine
			}

			return true;
		}



	}//	End class Speed_Booster_Pack
}    //	End if (!class_exists("Speed_Booster_Pack")) (1)

if ( class_exists( 'Speed_Booster_Pack' ) ) {

	// Installation and uninstallation hooks
	//register_activation_hook( __FILE__, array( 'Speed_Booster_Pack', 'sbp_activate' ) );
	//register_deactivation_hook( __FILE__, array( 'Speed_Booster_Pack', 'sbp_deactivate' ) );

	// instantiate the plugin class
	new Speed_Booster_Pack();

}    //	End if (!class_exists("Speed_Booster_Pack")) (2)
