(function ( $ ) {
	'use strict';

	$( window ).load(function() {
		// Encrypt message on submit.
		$( '#sform' ).submit( async function ( event ) {

			// This return prevents the submit event to refresh the page.
			event.preventDefault();

			// Reset validation helpers.
			$( '.form-group' ).removeClass( 'has-error' );
			$( '.help-block' ).remove();

			// Disable form
			$( '.secure-form :input' ).prop( 'disabled', true );

			let publicKey = await openpgp.readKey( { armoredKey: data.publicKeyArmored } );
			let message = $( '#message' ).val();

			const encrypted = await openpgp.encrypt({
				message: await openpgp.createMessage( { text: message } ),
				encryptionKeys: publicKey,
				//signingKeys: privateKey
			});

			let formData = {
				action: 'send_secure_form',
				name: $( '#name' ).val(),
				email: $( '#email' ).val(),
				subject: $( '#subject' ).val(),
				messageLen: message.length,
				message: JSON.stringify( encrypted ),
				security: data.nonce,
			};

			$.ajax({
				url: data.ajaxUrl,
				type: 'post',
				dataType: 'json',
				data: formData,
				success: function ( response ) {

					if ( ! response.success ) {
						if ( response.errors.name ) {
							$( '#name-group' ).addClass( 'has-error' );
							$( '#name-group' ).append(
								'<div class="help-block">' + response.errors.name + '</div>'
							);
						}

						if ( response.errors.email ) {
							$( '#email-group' ).addClass( 'has-error' );
							$( '#email-group' ).append(
								'<div class="help-block">' + response.errors.email + '</div>'
							);
						}

						if ( response.errors.subject ) {
							$( '#subject-group' ).addClass( 'has-error' );
							$( '#subject-group' ).append(
								'<div class="help-block">' + response.errors.subject + '</div>'
							);
						}

						if (response.errors.message) {
							$( '#message-group' ).addClass( 'has-error' );
							$( '#message-group' ).append(
								'<div class="help-block">' + response.errors.message + '</div>'
							);
						}

						if (response.errors.server) {
							// Give failed feedback to user
							$( '.secure-form' ).append( 
								'<div class="alert alert-danger">' + response.message + '</div>'
							);

							// Delete alert message
							setTimeout(function() { 
								$( '.secure-form .alert' ).remove();
							}, 10000);
						}
						
						// Enable form
						$( '.secure-form :input' ).prop( 'disabled', false );

					} else {
						$( '.secure-form' ).append( 
							'<div class="alert alert-success">' + response.message + '</div>'
						);

						// Reset form inputs
						$( '#message' ).val('');
						$( '#name' ).val('');
						$( '#email' ).val('');
						$( '#subject' ).val('');

						// Enable form
						$( '.secure-form :input' ).prop( 'disabled', false );

						// Delete alert message
						setTimeout(function() { 
							$( '.secure-form .alert' ).remove();
						}, 10000);
					}
				},
				error: function ( response ) {
					$( '.secure-form' ).append( 
						'<div class="alert alert-danger">' + response.message + '</div>'
					);

					// Enable form
					$( '.secure-form :input' ).prop( 'disabled', false );

					// Delete alert message
					setTimeout(function() { 
						$( '.secure-form .alert' ).remove();
					}, 10000);
				}
			});
		});
	});

})(jQuery);
