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

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;

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
	 * @var      Logger    $logger    The logger.
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

		$this->set_logger();

	}

	/**
	 * Initialize the Monolog logger.
	 *
	 * @since    1.0.0
	 */
	private function set_logger() {

		$upload_dir     = wp_upload_dir();
		$plugin_dirname = $upload_dir['basedir'] . '/' . $this->plugin_name;

		// Check folder or create.
		if ( ! file_exists( $plugin_dirname ) ) {
			wp_mkdir_p( $plugin_dirname );
		}

		// The default date format is "Y-m-d\TH:i:sP".
		$date_format = 'Y-m-d\TH:i:s';

		// the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
		// we now change the default output format according to our needs.
		$output = "[%datetime%] %level_name%: %message% %context%\n";

		// finally, create a formatter.
		$formatter = new LineFormatter( $output, $date_format );

		// Create a handler.
		$rotating_file = new RotatingFileHandler( $plugin_dirname . '/log.log', 7 );
		$rotating_file->setFormatter( $formatter );

		// bind it to a logger object.
		$this->logger = new Logger( 'plugin-log' );
		$this->logger->pushHandler( $rotating_file );

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

		wp_enqueue_script( 'openpgpjs', plugin_dir_url( dirname( __FILE__ ) ) . 'lib/js/openpgp.min.js', array(), '5.5.0', true );
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
		$form .= '<div id="message-group" class="form-group">';
		$form .= '<label for="message">' . esc_html__( 'Message', 'secure-encrypted-form' ) . '</label>';
		$form .= '<textarea id="message" name="message" rows="5"></textarea>';
		$form .= '</div>';
		$form .= '<form id="sform" method="post">';
		$form .= '<div id="subject-group" class="form-group">';
		$form .= '<label for="subject">' . esc_html__( 'Subject', 'secure-encrypted-form' ) . '</label>';
		$form .= '<input type="text" id="subject" name="subject">';
		$form .= '</div>';
		$form .= '<div id="name-group" class="form-group">';
		$form .= '<label for="name">' . esc_html__( 'Your name', 'secure-encrypted-form' ) . '</label>';
		$form .= '<input type="text" id="name" name="name">';
		$form .= '</div>';
		$form .= '<div id="email-group" class="form-group">';
		$form .= '<label for="email">' . esc_html__( 'Your email', 'secure-encrypted-form' ) . '</label>';
		$form .= '<input type="text" id="email" name="email">';
		$form .= '</div>';
		$form .= '<input type="hidden" id="encryptedMessage" name="encryptedMessage">';
		$form .= '<input type="submit" name="submit" value="' . esc_attr__( 'Submit', 'secure-encrypted-form' ) . '">';
		$form .= '</form>';
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
			$data['message'] = esc_html__( 'Error E1: secure encrypted message could not be sent, try again later.', 'secure-encrypted-form' );
		} else {

			// This is the email where you want to send the comments, defined in options.
			$to = get_option( 'secure_encrypted_form_option_name' )['email'];

			// Sanitize and define fields.
			$name_field    = sanitize_text_field( wp_unslash( $_POST['name'] ) );
			$email_field   = sanitize_email( wp_unslash( $_POST['email'] ) );
			$subject_field = sanitize_text_field( wp_unslash( $_POST['subject'] ) );
			$message_field = json_decode( sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) );

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
				'<a href="' . esc_url( 'https://charrua.es/' ) . '">',
				'</a>'
			);

			// Create file, rename it ans use it as attachment.
			$temp_file = wp_tempnam( 'secure-message' );
			$fileinfo  = pathinfo( $temp_file );
			$filename  = $fileinfo['dirname'] . '/' . $fileinfo['filename'] . '.txt.gpg';
			file_put_contents( $filename, $message_field );

			$attachments = array( $filename );

			// This are the message headers.
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'Reply-To: ' . $name_field . ' <' . $email_field . '>',
			);

			$sent = wp_mail( $to, $subject, $body, $headers, $attachments );

			// Log action.
			if ( $sent ) {
				$data['success'] = true;
				$data['message'] = esc_html__( 'Success: secure encrypted message sent.', 'secure-encrypted-form' );

				$this->logger->debug( 'Secure email sent: ', array( 'to' => $email_field ) );
			} else {

				// This would be the wp_mail function failing to send the email. Eg the email on settings is wrong.
				$errors['server'] = true;
				$data['success']  = false;
				$data['errors']   = $errors;
				$data['message']  = esc_html__( 'Error E2: secure encrypted message could not be sent, please contact website owner.', 'secure-encrypted-form' );

				// The error log is inyected by another function (debug_wp_mail_failure) to capture also the error code form wp_mail.
			}

			// Delete temp file (attachment).
			unlink( $filename );
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
		$to = $wp_error->error_data['wp_mail_failed']['to'];
		$this->logger->error( 'Secure email not sent: ', $to );
		$this->logger->error( 'Internal error code E2' );
		$this->logger->error( 'wp_mail: ', $wp_error->errors );
	}

}
