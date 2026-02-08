/**
 * Frontend JavaScript for Organizer Plugin.
 *
 * @package Organizer
 */

jQuery( document ).ready( function ( $ ) {

	// Handle Next Step button
	$( '.organizer-next-step' ).on( 'click', function ( e ) {
		e.preventDefault();

		const $form = $( this ).closest( 'form' );
		const $currentStep = $form.find( '.organizer-step.active' );
		const currentStepNum = parseInt( $currentStep.attr( 'data-step' ), 10 );
		const $nextStep = $form.find( '.organizer-step[data-step="' + ( currentStepNum + 1 ) + '"]' );

		if ( $nextStep.length ) {
			// Hide current step
			$currentStep.removeClass( 'active' );

			// Show next step
			$nextStep.addClass( 'active' );

			// Update progress bar
			const $progressSteps = $form.find( '.organizer-progress-step' );
			$progressSteps.each( function ( index ) {
				const stepNum = index + 1;
				if ( stepNum <= currentStepNum + 1 ) {
					$( this ).addClass( 'active' );
				} else {
					$( this ).removeClass( 'active' );
				}
			} );

			// Scroll to form
			$( 'html, body' ).animate(
				{
					scrollTop: $form.offset().top - 100,
				},
				500
			);
		}
	} );

	// Handle Previous Step button
	$( '.organizer-prev-step' ).on( 'click', function ( e ) {
		e.preventDefault();

		const $form = $( this ).closest( 'form' );
		const $currentStep = $form.find( '.organizer-step.active' );
		const currentStepNum = parseInt( $currentStep.attr( 'data-step' ), 10 );
		const $prevStep = $form.find( '.organizer-step[data-step="' + ( currentStepNum - 1 ) + '"]' );

		if ( $prevStep.length ) {
			// Hide current step
			$currentStep.removeClass( 'active' );

			// Show previous step
			$prevStep.addClass( 'active' );

			// Update progress bar
			const $progressSteps = $form.find( '.organizer-progress-step' );
			$progressSteps.each( function ( index ) {
				const stepNum = index + 1;
				if ( stepNum <= currentStepNum - 1 ) {
					$( this ).addClass( 'active' );
				} else {
					$( this ).removeClass( 'active' );
				}
			} );

			// Scroll to form
			$( 'html, body' ).animate(
				{
					scrollTop: $form.offset().top - 100,
				},
				500
			);
		}
	} );

	// Handle discount code application
	$( '#organizer_apply_discount' ).on( 'click', function ( e ) {
		e.preventDefault();

		const discountCode = $( '#organizer_discount_code' ).val();
		const $form = $( this ).closest( 'form' );
		const eventId = $form.find( 'input[name="event_id"]' ).val();
		const $messageDiv = $( '#organizer_discount_message' );
		const $priceDisplay = $( '#organizer_event_price_display' );

		if ( ! discountCode ) {
			$messageDiv.addClass( 'error' ).text( 'Please enter a discount code.' );
			return;
		}

		$.ajax( {
			url: organizer_ajax.ajaxurl,
			type: 'POST',
			data: {
				action: 'organizer_apply_discount',
				code: discountCode,
				event_id: eventId,
			},
			success: function ( response ) {
				if ( response.success ) {
					const newPrice = response.data.price;
					$priceDisplay.text( newPrice );
					$messageDiv
						.removeClass( 'error' )
						.addClass( 'success' )
						.text( response.data.message );
					$form.find( 'input[name="discount_code"]' ).val( discountCode );
				} else {
					$messageDiv.addClass( 'error' ).text( response.data.message );
				}
			},
			error: function () {
				$messageDiv.addClass( 'error' ).text( 'An error occurred. Please try again.' );
			},
		} );
	} );

} );
