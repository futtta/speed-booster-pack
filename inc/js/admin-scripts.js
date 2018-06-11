/**
 * The contents of this script only gets loaded on the plugin page
 */
(function( $ ) {

	'use strict';

	/**
	 * Handle UI tab switching via jQuery instead of relying on CSS only
	 */
	function AdminTabSwitching() {

		let NavTabSelector = '.nav-tab-wrapper a';

		/**
		 * Default tab handling
		 */

		// Make the first tab active by default
		$( NavTabSelector + ':first' ).addClass( 'nav-tab-active' );

		// Get the first tab href
		let InitialTabHref = $( NavTabSelector + ':first' ).attr( 'href' );

		// Make all the tabs, except the first one hidden
		$( '.sb-pack-tab' ).each( function( index, value ) {
			if ( '#' + $( this ).attr( 'id' ) !== InitialTabHref ) {
				$( this ).hide();
			}
		} );

		/**
		 * Listen for click events on nav-tab links
		 */
		$( NavTabSelector ).click( function( event ) {

			$( NavTabSelector ).removeClass( 'nav-tab-active' ); // Remove class from previous selector
			$( this ).addClass( 'nav-tab-active' ).blur(); // Add class to currently clicked selector

			let ClickedTab = $( this ).attr( 'href' );

			$( '.sb-pack-tab' ).each( function( index, value ) {
				if ( '#' + $( this ).attr( 'id' ) !== ClickedTab ) {
					$( this ).hide();
				}

				$( ClickedTab ).fadeIn();

			} );

			// Prevent default behavior
			event.preventDefault();

		} );
	}

	$( document ).ready( function() {
		AdminTabSwitching();
	} );

})( jQuery );