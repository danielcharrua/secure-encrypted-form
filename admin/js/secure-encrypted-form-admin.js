(function ( $ ) {
	'use strict';

	$( window ).load(function() {
		// Encrypt message on submit.
		$( '#secure-form-test' ).submit( async function ( event ) {

			// This return prevents the submit event to refresh the page.
			event.preventDefault();

			// Disable form
			$( '#secure-form-test :input' ).prop( 'disabled', true );

			let publicKey = await openpgp.readKey( { armoredKey: data.publicKeyArmored } );
			let message = "This is a test message :)";

			const encrypted = await openpgp.encrypt({
				message: await openpgp.createMessage( { text: message } ),
				encryptionKeys: publicKey,
				//signingKeys: privateKey
			});

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

					// Delete alert message
					setTimeout(function() { 
						$( '#secure-form-test .alert' ).remove();
					}, 10000);
				}
			});
		});
	});

})(jQuery);
