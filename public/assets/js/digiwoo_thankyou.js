(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
 jQuery(document).ready(function($) {
        if ($('body').hasClass('ypf-apg-order-failed')) {
            $('.ypf-apg-icon i').removeClass('fa-check-circle').addClass('fa-times-circle');
            $('.ypf-apg-heading-text h2').text('Oops! Something Went Wrong');
            $('.ypf-apg-content-text-1 p').text('Your payment could not be processed at this time. This might be due to a variety of reasons, such as insufficient funds, incorrect card details. Please check your payment information and try again, or consider using an alternative payment method.');
            $('.ypf-apg-heading-text-content p, .ypf-apg-content-divider').css('visibility', 'hidden');
        }
    });

})( jQuery );
