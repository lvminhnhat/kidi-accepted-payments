/**
 * Kidi Accepted Payments — Customizer Live Preview
 *
 * Binds postMessage transport so changes in the Customizer
 * panel are reflected instantly without a full page reload.
 *
 * @package Kidi_Accepted_Payments
 * @since   1.0.0
 */

/* global wp, jQuery */
( function( $ ) {
	'use strict';

	// Label text
	wp.customize( 'kidi_ap_settings[label]', function( value ) {
		value.bind( function( newVal ) {
			var $label = $( '.kidi-ap__label' );
			if ( newVal ) {
				$label.text( newVal ).show();
			} else {
				$label.hide();
			}
		} );
	} );

	// Payment method toggles
	var methods = [ 'visa', 'mastercard', 'amex', 'paypal', 'applepay', 'googlepay' ];

	$.each( methods, function( _i, method ) {
		wp.customize( 'kidi_ap_settings[' + method + ']', function( value ) {
			value.bind( function( newVal ) {
				var $icon = $( '.kidi-ap__icon[data-method="' + method + '"]' );
				if ( newVal ) {
					$icon.show();
				} else {
					$icon.hide();
				}
			} );
		} );
	} );

} )( jQuery );
