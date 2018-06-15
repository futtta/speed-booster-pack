<?php

class Old {

	define( 'APHMS_PLUGIN_DIR_NAME', 'speed-booster-pack' );
	define( 'APHMS_PLUGIN_FILE_NAME', 'speed-booster-pack.php' );
	define( 'APHMS_DS', DIRECTORY_SEPARATOR );
	define( 'APHMS_PLUGIN_PATH', WP_PLUGIN_DIR . APHMS_DS . APHMS_PLUGIN_DIR_NAME );
	define( 'APHMS_PLUGIN_URL', WP_PLUGIN_URL . '/' . APHMS_PLUGIN_DIR_NAME );


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

		$buffer = Minify_HTML::minify(
			$buffer, array(
				'jsMinifier'  => array( 'JSMin', 'minify' ),
				'cssMinifier' => array( 'Minify_CSS', 'minify' ),
			)
		);

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

}
