(function ( $ ) {
	'use strict';

	$( window ).load(function() {
		// Encrypt message on submit.
		$( '#secure-form-test' ).submit( async function ( event ) {

			// This return prevents the submit event to refresh the page.
			event.preventDefault();

			// Browsers only expose WebCrypto in a secure context, and OpenPGP.js
			// refuses to run without it. Checking here keeps the failure honest:
			// otherwise it surfaces as an error blaming the public key.
			if ( ! window.isSecureContext ) {
				$( '#secure-form-test' ).append(
					'<div class="alert alert-danger">' + data.errorNoSecureCtx + '</div>'
				);

				return;
			}

			// Disable form
			$( '#secure-form-test :input' ).prop( 'disabled', true );
			$( '#secure-form-test .spinner' ).addClass( 'is-active' );

			// Read public key
			let publicKey;
			
			try {
				publicKey = await openpgp.readKey( { armoredKey: data.publicKeyArmored } );
			} catch (error) {

				// Give failed feedback to user (Error E4). The underlying message is
				// shown too: this is the admin screen, and without it every failure
				// looks like a bad key even when it is something else entirely.
				$( '#secure-form-test' ).append(
					'<div class="alert alert-danger">' + data.errorOnKey + ' (' + error.message + ')</div>'
				);

				// Enable form
				$( '#secure-form-test :input' ).prop( 'disabled', false );
				$( '#secure-form-test .spinner' ).removeClass( 'is-active' );

				return;
			}

			let message = "This is a test message :)";

			let encrypted;
			try {
				encrypted = await openpgp.encrypt({
					message: await openpgp.createMessage( { text: message } ),
					encryptionKeys: publicKey,
				});
			} catch (error) {
				$( '#secure-form-test' ).append(
					'<div class="alert alert-danger">' + data.errorOnKey + ' (' + error.message + ')</div>'
				);
				$( '#secure-form-test :input' ).prop( 'disabled', false );
				$( '#secure-form-test .spinner' ).removeClass( 'is-active' );
				setTimeout(function() {
					$( '#secure-form-test .alert' ).remove();
				}, 10000);
				return;
			}

			let formData = {
				action: 'send_secure_test_form',
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

						if (response.errors.server) {
							// Give failed feedback to user
							$( '#secure-form-test' ).append( 
								'<div class="alert alert-danger">' + response.message + '</div>'
							);

							// Enable form
							$( '#secure-form-test :input' ).prop( 'disabled', false );
							$( '#secure-form-test .spinner' ).removeClass( 'is-active' );

							// Delete alert message
							setTimeout(function() { 
								$( '#secure-form-test .alert' ).remove();
							}, 10000);
						}
						
					} else {
						$( '#secure-form-test' ).append( 
							'<div class="alert alert-success">' + response.message + '</div>'
						);

						// Enable form
						$( '#secure-form-test :input' ).prop( 'disabled', false );
						$( '#secure-form-test .spinner' ).removeClass( 'is-active' );

						// Delete alert message
						setTimeout(function() { 
							$( '#secure-form-test .alert' ).remove();
						}, 10000);
					}
				},
				error: function ( response ) {
					$( '#secure-form-test' ).append( 
						'<div class="alert alert-danger">' + response.message + '</div>'
					);

					// Enable form
					$( '#secure-form-test :input' ).prop( 'disabled', false );
					$( '#secure-form-test .spinner' ).removeClass( 'is-active' );

					// Delete alert message
					setTimeout(function() { 
						$( '#secure-form-test .alert' ).remove();
					}, 10000);
				}
			});
		});
	});

})(jQuery);
