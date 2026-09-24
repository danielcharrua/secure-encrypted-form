<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://charrua.es
 * @since      1.0.0
 *
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/public
 * @author     Daniel Pereyra Costas <hola@charrua.es>
 */
class Secure_Encrypted_Form_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The logger.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Secure_Encrypted_Form_Logger    $logger    The logger.
	 */
	private $logger;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @param   string $plugin_name The name of the plugin.
	 * @param   string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->logger = new Secure_Encrypted_Form_Logger();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Secure_Encrypted_Form_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Secure_Encrypted_Form_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/secure-encrypted-form-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Secure_Encrypted_Form_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Secure_Encrypted_Form_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( Secure_Encrypted_Form_Turnstile::is_enabled() ) {
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Cloudflare versions this URL itself, appending ours would be wrong.
			wp_enqueue_script( 'cloudflare-turnstile', Secure_Encrypted_Form_Turnstile::SCRIPT_URL, array(), null, true );
		}

		wp_enqueue_script( 'openpgpjs', plugin_dir_url( __DIR__ ) . 'lib/js/openpgp.min.js', array(), '6.3.0', true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/secure-encrypted-form-public.js', array( 'jquery', 'openpgpjs' ), $this->version, false );

		/**
		 * Define variables passed to JS.
		 */

		wp_localize_script(
			$this->plugin_name,
			'data',
			array(
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'secure_form_nonce' ),
				'publicKeyArmored' => get_option( 'secure_encrypted_form_option_name' )['public_key'],
				'errorOnKey'       => esc_html__( 'Error: it seems to be an error/typo on the encryption key. Please contact the web administrator.', 'secure-encrypted-form' ),
				'errorOnEncrypt'   => esc_html__( 'Error: the message could not be encrypted, the encryption key may have expired. Please contact the web administrator.', 'secure-encrypted-form' ),
				'errorNoSecureCtx' => esc_html__( 'Error: this page is not served over a secure connection (HTTPS), so your browser will not allow the message to be encrypted. Nothing has been sent. Please contact the web administrator.', 'secure-encrypted-form' ),
				'turnstileEnabled' => Secure_Encrypted_Form_Turnstile::is_enabled(),
			)
		);
	}

	/**
	 * Display content from short code
	 *
	 * @since   1.0.0
	 * @param   string $atts The shortcode attributes.
	 */
	public function secure_encrypted_form_shortcode( $atts ) {

		$form  = '<div class="secure-form">';
		$form .= '<form id="sform" method="post">';
		$form .= '<div id="name-group" class="form-group">';
		$form .= '<label for="name">' . esc_html__( 'Your name', 'secure-encrypted-form' ) . '</label>';
		$form .= '<input type="text" id="name" name="name">';
		$form .= '</div>';
		$form .= '<div id="email-group" class="form-group">';
		$form .= '<label for="email">' . esc_html__( 'Your email', 'secure-encrypted-form' ) . '</label>';
		$form .= '<input type="email" id="email" name="email">';
		$form .= '</div>';
		$form .= '<div id="subject-group" class="form-group">';
		$form .= '<label for="subject">' . esc_html__( 'Subject', 'secure-encrypted-form' ) . '</label>';
		$form .= '<input type="text" id="subject" name="subject">';
		$form .= '</div>';
		$form .= '<input type="hidden" id="encryptedMessage" name="encryptedMessage">';
		$form .= Secure_Encrypted_Form_Turnstile::get_widget_markup();
		$form .= '</form>';
		$form .= '<div id="message-group" class="form-group">';
		$form .= '<label for="message">' . esc_html__( 'Message', 'secure-encrypted-form' ) . '</label>';
		$form .= '<textarea id="message" name="message" rows="5"></textarea>';
		$form .= '</div>';
		$form .= '<input type="submit" form="sform" name="submit" value="' . esc_attr__( 'Submit', 'secure-encrypted-form' ) . '">';
		$form .= '</div>';

		return $form;
	}

	/**
	 * Ajax function to process form
	 *
	 * @since   1.0.0
	 */
	public function send_secure_form() {

		// This is a secure process to validate if this request comes from a valid source.
		check_ajax_referer( 'secure_form_nonce', 'security' );

		// Spam check, before anything else is done with the submission. It has to
		// happen here and not in the browser: a bot would just skip it there.
		if ( Secure_Encrypted_Form_Turnstile::is_enabled() ) {
			$turnstile = new Secure_Encrypted_Form_Turnstile( $this->logger );
			$token     = isset( $_POST[ Secure_Encrypted_Form_Turnstile::TOKEN_FIELD ] )
				? sanitize_text_field( wp_unslash( $_POST[ Secure_Encrypted_Form_Turnstile::TOKEN_FIELD ] ) )
				: '';

			if ( ! $turnstile->verify( $token ) ) {
				echo wp_json_encode(
					array(
						'success' => false,
						'errors'  => array( 'turnstile' => true ),
						'message' => esc_html__( 'Error: the spam check could not be completed, please try again.', 'secure-encrypted-form' ),
					)
				);
				wp_die();
			}
		}

		// Activate wp_mail errors.
		add_action( 'wp_mail_failed', array( $this, 'debug_wp_mail_failure' ) );

		$errors = array();
		$data   = array();

		if ( empty( $_POST['name'] ) ) {
			$errors['name'] = esc_html__( 'Please fill your name.', 'secure-encrypted-form' );
		}

		if ( empty( $_POST['email'] ) ) {
			$errors['email'] = esc_html__( 'Please fill your email.', 'secure-encrypted-form' );
		}

		if ( empty( $_POST['subject'] ) ) {
			$errors['subject'] = esc_html__( 'Please fill your subject.', 'secure-encrypted-form' );
		}

		if ( empty( $_POST['messageLen'] ) || empty( $_POST['message'] ) ) {
			$errors['message'] = esc_html__( 'Please fill your message.', 'secure-encrypted-form' );
		}

		if ( ! empty( $errors ) ) {
			$data['success'] = false;
			$data['errors']  = $errors;
			$data['message'] = esc_html__( 'Validation error: please check form fields for feedback.', 'secure-encrypted-form' );
		} else {

			// This is the email where you want to send the comments, defined in options.
			$to = get_option( 'secure_encrypted_form_option_name' )['email'];

			// Sanitize and define fields.
			$name_field    = sanitize_text_field( wp_unslash( $_POST['name'] ) );
			$email_field   = sanitize_email( wp_unslash( $_POST['email'] ) );
			$subject_field = sanitize_text_field( wp_unslash( $_POST['subject'] ) );
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$message_field = sanitize_textarea_field( json_decode( wp_unslash( $_POST['message'] ) ) );

			// Message subject.
			$subject = esc_html__( 'Secure message:', 'secure-encrypted-form' ) . ' ' . $subject_field;

			// Message body.
			$body  = esc_html__( 'From:', 'secure-encrypted-form' ) . ' ' . $name_field . '<br>';
			$body .= 'Email: ' . $email_field . '<br><br>';
			$body .= esc_html__( 'Please find the message attached.', 'secure-encrypted-form' ) . '<br><br>';
			$body .= '--<br>';
			$body .= sprintf(
				/* translators: %1$s is the plugin name and %2$s is the plugin version */
				esc_html__(
					'Sent with %1$s for WordPress v%2$s',
					'secure-encrypted-form'
				),
				esc_html( $this->plugin_name ),
				esc_html( $this->version )
			);
			$body .= sprintf(
				/* translators: %1$s and %2$s are HTML a tags */
				esc_html__(
					'%1$sIf you find this piece of software usefull please consider %2$sdonating to the author%3$s.',
					'secure-encrypted-form'
				),
				'<br>',
				'<a href="' . esc_url( 'https://charrua.es/donaciones' ) . '">',
				'</a>'
			);

			// Create file, rename it ans use it as attachment.
			$temp_file = wp_tempnam( 'secure-message' );
			$fileinfo  = pathinfo( $temp_file );
			$filename  = $fileinfo['dirname'] . '/' . $fileinfo['filename'] . '.txt.gpg';
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Writing a local temp file for the mail attachment.
			file_put_contents( $filename, $message_field );

			$attachments = array( $filename );

			// This are the message headers.
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'Reply-To: ' . $name_field . ' <' . $email_field . '>',
			);

			// Try to send mail.
			// Also diagnose if PHP mail() function is disabled pn webhost.
			// Anything else that goes wrong is reported as a regular sending error, so
			// the visitor always gets an answer and the cause ends up in the log.
			$sent = false;

			try {
				$sent = wp_mail( $to, $subject, $body, $headers, $attachments );
			} catch ( Throwable $e ) {
				if ( str_contains( $e->getMessage(), 'Call to undefined function PHPMailer\PHPMailer\mail()' ) ) {
					$sent = 'php_mail_fail';
				} else {
					$this->logger->error( 'wp_mail threw an exception: ', array( 'error' => $e->getMessage() ) );
				}
			}

			// User feedback and log.
			if ( true === $sent ) {

				$data['success'] = true;
				$data['message'] = esc_html__( 'Success: secure encrypted message sent.', 'secure-encrypted-form' );

				$this->logger->debug( 'Secure email sent.' );

			} elseif ( false === $sent ) {

				// This would be the wp_mail function failing to send the email. Eg the email on settings is wrong.
				$errors['server'] = true;
				$data['success']  = false;
				$data['errors']   = $errors;
				$data['message']  = esc_html__( 'Error: secure encrypted message could not be sent, please contact website owner.', 'secure-encrypted-form' );

				// The error log is inyected by another function (debug_wp_mail_failure) to capture also the error code form wp_mail.
			} elseif ( 'php_mail_fail' === $sent ) {

				$errors['server'] = true;
				$data['success']  = false;
				$data['errors']   = $errors;
				$data['message']  = esc_html__( 'Error: secure encrypted message could not be sent, please contact website owner.', 'secure-encrypted-form' );

				$this->logger->error( 'Secure email not sent.' );
				$this->logger->error( 'PHP mail() function is disabled on webhost.' );
			}

			// Delete temp file (attachment).
			wp_delete_file( $filename );
		}

		// Disable wp_mail capture errors.
		remove_action( 'wp_mail_failed', array( $this, 'debug_wp_mail_failure' ) );

		echo wp_json_encode( $data );
		wp_die();
	}

	/**
	 * Debug wp_mail failure and log them.
	 *
	 * @since   1.0.0
	 * @param   WP_Error $wp_error The error object.
	 */
	public function debug_wp_mail_failure( $wp_error ) {
		$this->logger->error( 'Secure email not sent.' );
		$this->logger->error( 'Internal error code E2' );
		$this->logger->error( 'wp_mail: ', array( 'error' => $wp_error->get_error_message() ) );
	}
}
