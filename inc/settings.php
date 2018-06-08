<?php

if ( ! class_exists( 'Speed_Booster_Pack_Options' ) ) {

	class Speed_Booster_Pack_Options {

		private $sbp_options;

		/*--------------------------------------------------------------------------------------------------------
			Construct the plugin object
		---------------------------------------------------------------------------------------------------------*/

		public function __construct() {

			add_action( 'admin_init', array( $this, 'sbp_admin_init' ) );
			add_action( 'admin_menu', array( $this, 'sbp_add_menu' ) );

		}   //  END public function __construct


		public function sbp_admin_init() {

			register_setting( 'speed_booster_settings_group', 'sbp_settings' );
			register_setting( 'speed_booster_settings_group', 'sbp_css_exceptions' );

		}  //  END public function admin_init


		/*--------------------------------------------------------------------------------------------------------
			Sanitize Options
		---------------------------------------------------------------------------------------------------------*/

		public function sbp_sanitize( $input ) {

			return $input;

		}


		/*--------------------------------------------------------------------------------------------------------
			// Add a page to manage the plugin's settings
		---------------------------------------------------------------------------------------------------------*/

		public function sbp_add_menu() {

			global $sbp_settings_page;
			$sbp_settings_page = add_menu_page( __( 'Speed Booster Options', 'sb-pack' ), __( 'Speed Booster', 'sb-pack' ), 'manage_options', 'sbp-options', array(
				$this,
				'sbp_plugin_settings_page',
			), plugin_dir_url( __FILE__ ) . 'images/icon-16x16.png' );

		}   //  END public function add_menu()


		public function sbp_plugin_settings_page() {

			/*--------------------------------------------------------------------------------------------------------
				Global Variables used on options HTML page
			---------------------------------------------------------------------------------------------------------*/

			global $sbp_options;


			// Render the plugin options page HTML
			include( SPEED_BOOSTER_PACK_PATH . 'inc/template/options.php' );

		} // END public function sbp_plugin_settings_page()


	}   //  END class Speed_Booster_Pack_Options

}   //  END if(!class_exists('Speed_Booster_Pack_Options'))
