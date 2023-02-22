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
			$( '.secure-form' ).append( '<div class="spinner-wrapper"><span class="spinner"></span></div>' );

			// Read public key
			let publicKey;
			
			try {
				publicKey = await openpgp.readKey( { armoredKey: data.publicKeyArmored } );
			} catch (error) {
				// Give failed feedback to user
				$( '.secure-form' ).append( 
					'<div class="alert alert-danger">' + data.errorOnKey + '</div>'
				);

				$( '.spinner-wrapper' ).remove();

				return;
			}


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

						// Give failed feedback to user
						$( '.secure-form' ).append( 
							'<div class="alert alert-danger">' + response.message + '</div>'
						);

						// Delete alert message
						setTimeout(function() { 
							$( '.secure-form .alert' ).remove();
						}, 10000);
						
						// Enable form
						$( '.secure-form :input' ).prop( 'disabled', false );
						$( '.spinner-wrapper' ).remove();

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
						$( '.spinner-wrapper' ).remove();

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
					$( '.spinner-wrapper' ).remove();

					// Delete alert message
					setTimeout(function() { 
						$( '.secure-form .alert' ).remove();
					}, 10000);
				}
			});
		});
	});

})(jQuery);
