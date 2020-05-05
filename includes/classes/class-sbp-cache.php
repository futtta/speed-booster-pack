<?php

class SBP_Cache {
	private $options = [
		'cache-expire-time'       => 604800, // Expire time in seconds
		// Bypass options
		'disable_cache_on_login'  => false,
		'disable_cache_on_mobile' => false,
	];

	public function __construct() {
		// Set caching options
		$this->set_options();

		// Set admin bar links
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_links' ] );

		// Clear cache hook
		add_action( 'admin_init', [ $this, 'clear_total_cache' ] );

		// Handle The Cache
		add_filter( 'sbp_output_buffer', [ $this, 'handle_cache' ] );
	}

	public static function instantiate() {
		new Self();
	}

	private function should_bypass_cache() {
		// Check if cache is enabled
		if ( ! sbp_get_option( 'enable-cache' ) ) {
			return true;
		}

		// Do not cache for logged in users
		if ( is_user_logged_in() ) {
			return true;
		}

		// Do not cache administrator
		if ( user_can( get_current_user_id(), 'administrator' ) ) {
			return true;
		}

		// Check for several special pages
		if ( is_search() || is_404() || is_feed() || is_trackback() || is_robots() || is_preview() || post_password_required() ) {
			return true;
		}

		// DONOTCACHEPAGE
		if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE === true ) {
			return true;
		}

		// Woocommerce checkout check
		if ( function_exists( 'is_checkout' ) ) {
			if ( is_checkout() ) {
				return true;
			}
		}

		// Woocommerce cart check
		if ( function_exists( 'is_cart' ) ) {
			if ( is_cart() ) {
				return true;
			}
		}

		// Check request method. Only cache get methods
		if ( $_SERVER['REQUEST_METHOD'] != 'GET' ) {
			return true;
		}

		// Check for UTM parameters for affiliates
		if ( isset( $_GET['utm_source'] ) || isset( $_GET['utm_medium'] ) || isset( $_GET['utm_campaign'] ) || isset( $_GET['utm_term'] ) || isset( $_GET['utm_content'] ) ) {
			return true;
		}

		// Check if query string exists
		if ( count( $_GET ) > 0 ) {
			return true;
		}

		return false;
	}

	private function get_filesystem() {
		global $wp_filesystem;

		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem();

		return $wp_filesystem;
	}

	private function set_options() {
		global $sbp_options;
		$setting_names = [
			'cache-expire-time',
			'cache_file_name',
			'cache_gzip_file_name',
			'cache_do_not_logged_in',
			'cache_separate_mobile',
			'cache_do_not_query_string',
		];

		foreach ( $setting_names as $name ) {
			if ( sbp_get_option( $name ) ) {
				$this->options[ $name ] = sbp_get_option( $name );
			}
		}
	}

	public function admin_bar_links( $admin_bar ) {
		$cache_items = [
			'id'     => 'sbp_clear_cache',
			'parent' => 'speed_booster_pack',
			'title'  => __( 'Clear Cache', 'speed-booster-pack' ),
			'href'   => admin_url( 'admin.php?page=sbp-options&sbp_action=sbp_clear_cache' )
		];

		$admin_bar->add_menu( $cache_items );
	}

	public function clear_total_cache() {
		if ( isset( $_GET['sbp_action'] ) && $_GET['sbp_action'] == 'sbp_clear_cache' ) {
			self::delete_dir( SBP_CACHE_DIR );
			wp_redirect( admin_url( 'admin.php?page=sbp-options#sbp-cache' ) );
		}
	}

	private function delete_dir( $dir ) {
		$filesystem = $this->get_filesystem();

		return $filesystem->rmdir( $dir, true );
	}

	public function start_buffer() {
		if ( $this->should_bypass_cache() ) {
			return;
		}

		ob_start( [ $this, 'handle_cache' ] );
	}

	public function handle_cache( $html ) {
		if ( $this->should_bypass_cache() ) {
			return $html;
		}

		global $wp_filesystem;


		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem();

		// Read cache file
		$cache_file_path = $this->get_cache_file_path() . 'index.html';

		$has_file_expired = $wp_filesystem->mtime( $cache_file_path ) + $this->options['cache-expire-time'] < time();

		if ( $wp_filesystem->exists( $cache_file_path ) && ! $has_file_expired ) {
			header( 'X-Cache-Provider: Speed-Booster-Pack' );

			return $wp_filesystem->get_contents( $cache_file_path );
		}

		// Apply filters
		$html = apply_filters( 'sbp_cache_before_create', $html );
		$this->create_cache_file( $html );

		return $html;
	}

	private function create_cache_file( $html ) {
		$dir_path  = $this->get_cache_file_path();
		$file_path = $dir_path . 'index.html';

		wp_mkdir_p( $dir_path );
		$file = @fopen( $file_path, 'w+' );
		fwrite( $file, $html );
		fclose( $file );
	}

	private function get_cache_file_path() {
		$cache_dir = SBP_CACHE_DIR;
		if ( wp_is_mobile() && sbp_get_option( 'separate-mobile-cache', false ) ) {
			$cache_dir = SBP_CACHE_DIR . '/.mobile';
		}

		$path = sprintf(
			'%s%s%s%s',
			$cache_dir,
			DIRECTORY_SEPARATOR,
			parse_url(
				'http://' . strtolower( $_SERVER['HTTP_HOST'] ),
				PHP_URL_HOST
			),
			parse_url(
				$_SERVER['REQUEST_URI'],
				PHP_URL_PATH
			)
		);

		if ( is_file( $path ) > 0 ) {
			wp_die( 'Error occured on SBP cache. Please contact you webmaster.' );
		}

		return rtrim( $path, "/" ) . "/";
	}
}