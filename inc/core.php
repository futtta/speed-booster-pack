<?php

/*--------------------------------------------------------------------------------------------------------
	Plugin Core Functions
---------------------------------------------------------------------------------------------------------*/

if ( ! class_exists( 'Speed_Booster_Pack_Core' ) ) {

	class Speed_Booster_Pack_Core {

		public function __construct() {

			global $sbp_options;

			add_action( 'wp_enqueue_scripts', array( $this, 'sbp_no_more_fontawesome' ), 9999 );
			add_action( 'after_setup_theme', array( $this, 'sbp_junk_header_tags' ) );

			$this->sbp_css_optimizer(); // CSS Optimizer functions

			// Minifier
			if ( ! is_admin() and isset( $sbp_options['minify_html_js'] ) ) {
				$this->sbp_minifier();
			}

			//	Remove query strings from static resources
			if ( ! is_admin() and isset( $sbp_options['query_strings'] ) ) {
				add_filter( 'script_loader_src', array( $this, 'sbp_remove_query_strings' ), 15, 1 );
				add_filter( 'style_loader_src', array( $this, 'sbp_remove_query_strings' ), 15, 1 );
			}

			/**
			 * @since 3.7
			 */
			// Disable emojis
			if ( ! is_admin() && isset( $sbp_options['remove_emojis'] ) ) {
				add_action( 'init', array( $this, 'sbp_disable_emojis' ) );
			}

			/**
			 * @since 3.7
			 */
			// Disable XML-RPC
			if ( ! isset( $sbp_options['disable_xmlrpc'] ) ) {
				add_filter( 'xmlrpc_enabled', '__return_false' );
				add_filter( 'wp_headers', array( $this, 'sbp_remove_x_pingback' ) );
				add_filter( 'pings_open', '__return_false', 9999 );
			}

		}  //  END public public function __construct

		/*--------------------------------------------------------------------------------------------------------
			Minify HTML and Javascripts
		---------------------------------------------------------------------------------------------------------*/

		function sbp_minifier() {

			require_once( SPEED_BOOSTER_PACK_PATH . 'inc/sbp-minifier.php' );
		}    //	End function sbp_minifier()


		/*--------------------------------------------------------------------------------------------------------
			CSS Optimizer
		---------------------------------------------------------------------------------------------------------*/

		function sbp_css_optimizer() {

			require_once( SPEED_BOOSTER_PACK_PATH . 'inc/css-optimizer.php' );

		}    //	End function sbp_css_optimizer()


		/*--------------------------------------------------------------------------------------------------------
			Remove query strings from static resources
		---------------------------------------------------------------------------------------------------------*/

		function sbp_remove_query_strings( $src ) {
			//	remove "?ver" string

			$output = preg_split( '/(\?rev|&ver|\?ver)/', $src );

			return $output[0];

		}

		/*--------------------------------------------------------------------------------------------------------
			Disable Emoji
		---------------------------------------------------------------------------------------------------------*/

		function sbp_disable_emojis() {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

			add_filter( 'tiny_mce_plugins', array( $this, 'sbp_disable_emojis_tinymce' ) );
			add_filter( 'wp_resource_hints', array( $this, 'sbp_disable_emojis_dns_prefetch' ), 10, 2 );
		}

		function sbp_disable_emojis_tinymce( $plugins ) {
			if ( is_array( $plugins ) ) {
				return array_diff( $plugins, array( 'wpemoji' ) );
			} else {
				return array();
			}
		}

		function sbp_disable_emojis_dns_prefetch( $urls, $relation_type ) {
			if ( 'dns-prefetch' == $relation_type ) {
				$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2.2.1/svg/' );
				$urls          = array_diff( $urls, array( $emoji_svg_url ) );
			}

			return $urls;
		}

		/*--------------------------------------------------------------------------------------------------------
			Disable XML-RPC
		---------------------------------------------------------------------------------------------------------*/

		function sbp_remove_x_pingback( $headers ) {
			unset( $headers['X-Pingback'] );

			return $headers;
		}

		/*--------------------------------------------------------------------------------------------------------
			Dequeue extra Font Awesome stylesheet
		---------------------------------------------------------------------------------------------------------*/

		function sbp_no_more_fontawesome() {
			global $wp_styles;
			global $sbp_options;

			// we'll use preg_match to find only the following patterns as exact matches, to prevent other plugin stylesheets that contain font-awesome expression to be also dequeued
			$patterns = array(
				'font-awesome.css',
				'font-awesome.min.css',
			);
			//	multiple patterns hook
			$regex = '/(' . implode( '|', $patterns ) . ')/i';
			foreach ( $wp_styles->registered as $registered ) {
				if ( ! is_admin() and preg_match( $regex, $registered->src ) and isset( $sbp_options['font_awesome'] ) ) {
					wp_dequeue_style( $registered->handle );
					// FA was dequeued, so here we need to enqueue it again from CDN
					wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
				}    //	END if( preg_match...
			}    //	END foreach
		}    //	End function dfa_no_more_fontawesome


		/*--------------------------------------------------------------------------------------------------------
			Remove junk header tags
		---------------------------------------------------------------------------------------------------------*/

		public function sbp_junk_header_tags() {

			global $sbp_options;

			//	Remove Adjacent Posts links PREV/NEXT
			if ( isset( $sbp_options['remove_adjacent'] ) ) {
				remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
			}

			//	Remove Windows Live Writer Manifest Link
			if ( isset( $sbp_options['wml_link'] ) ) {
				remove_action( 'wp_head', 'wlwmanifest_link' );
			}

			// Remove RSD (Really Simple Discovery) Link
			if ( isset( $sbp_options['rsd_link'] ) ) {
				remove_action( 'wp_head', 'rsd_link' );
			}

			//	Remove WordPress Shortlinks from WP Head
			if ( isset( $sbp_options['remove_wsl'] ) ) {
				remove_action( 'wp_head', 'wp_shortlink_wp_head' );
			}

			//	Remove WP Generator/Version - for security reasons and cleaning the header
			if ( isset( $sbp_options['wp_generator'] ) ) {
				remove_action( 'wp_head', 'wp_generator' );
			}

			//	Remove all feeds
			if ( isset( $sbp_options['remove_all_feeds'] ) ) {
				remove_action( 'wp_head', 'feed_links_extra', 3 );    // remove the feed links from the extra feeds such as category feeds
				remove_action( 'wp_head', 'feed_links', 2 );        // remove the feed links from the general feeds: Post and Comment Feed
			}

		}    //	END public function sbp_junk_header_tags
	}   //  END class Speed_Booster_Pack_Core
}   //  END if(!class_exists('Speed_Booster_Pack_Core'))
