<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://charrua.es
 * @since      1.0.0
 *
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/admin
 */

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/admin
 * @author     Daniel Pereyra Costas <hola@charrua.es>
 */
class Secure_Encrypted_Form_Admin {

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
	 * The options of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $options    The options of this plugin.
	 */
	private $options;

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
	 * @since    1.0.0
	 * @param    string $plugin_name    The name of this plugin.
	 * @param    string $version        The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->options     = get_option( 'secure_encrypted_form_option_name' );

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
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/secure-encrypted-form-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/secure-encrypted-form-admin.js', array( 'jquery', 'openpgpjs' ), $this->version, false );

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
	 * Check if options are defined and are not empty.
	 *
	 * @since    1.0.0
	 */
	public function show_incomplete_settings_notice() {

		if ( ! is_array( $this->options )
			|| empty( $this->options['email'] )
			|| empty( $this->options['public_key'] ) ) {

				$class     = 'notice notice-error';
				$message   = __( 'Secure Encrypted Form: please complete settings to use the secure form in your website.', 'secure-encrypted-form' );
				$url       = '/wp-admin/admin.php?page=secure-encrypted-form';
				$link_text = __( 'Complete setup.', 'secure-encrypted-form' );

				printf( '<div class="%1$s"><p>%2$s <a href="%3$s">%4$s</a></p></div>', esc_attr( $class ), esc_html( $message ), esc_url( $url ), esc_html( $link_text ) );
		}

	}

	/**
	 * Add plugin admin settings page under tools.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_settings_page() {

		add_menu_page(
			'Secure Encrypted Form',
			'Secure Encrypted Form',
			'manage_options',
			'secure-encrypted-form',
			array( $this, 'secure_encrypted_form_settings_page' ),
			'dashicons-lock',
		);

		add_submenu_page(
			'secure-encrypted-form',
			'Secure Encrypted Form log',
			'Debug log',
			'manage_options',
			'secure-encrypted-form-debug-log',
			array( $this, 'secure_encrypted_form_debug_log_page' ),
		);
	}

	/**
	 * Display settings form and content.
	 *
	 * @since    1.0.0
	 */
	public function secure_encrypted_form_settings_page() {

		require_once plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-settings.php';

	}

	/**
	 * Display plugin log page.
	 *
	 * @since    1.0.0
	 */
	public function secure_encrypted_form_debug_log_page() {

		// Read the log files from the folder.
		$logs = $this->get_debug_logs();

		$selected_log_content = false;
		$selected_log         = false;

		if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'sef-debug-logs' ) ) {

			if ( isset( $_POST['debug_log_files'] ) ) {

				$selected_log = sanitize_text_field( wp_unslash( $_POST['debug_log_files'] ) );

				$selected_log_content = esc_html( $this->read_debug_log( $selected_log ) );
			}
		}

		require_once plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-debug-log.php';

	}

	/**
	 * Get debug log files.
	 *
	 * @since    1.0.0
	 */
	public function get_debug_logs() {
		$logs = array();

		// Get a list of all the log files in the folder.
		$upload_dir     = wp_upload_dir();
		$plugin_dirname = $upload_dir['basedir'] . '/secure-encrypted-form';
		$files          = scandir( $plugin_dirname );

		// Read the contents of each log file.
		foreach ( $files as $file ) {
			if ( '.' !== $file && '..' !== $file ) {
				array_push( $logs, $file );
			}
		}

		return $logs;
	}

	/**
	 * Get log file content.
	 *
	 * @since    1.0.0
	 * @param    string $filename    The name of the log file.
	 */
	public function read_debug_log( $filename ) {

		$upload_dir     = wp_upload_dir();
		$plugin_dirname = $upload_dir['basedir'] . '/secure-encrypted-form';

		return file_get_contents( $plugin_dirname . '/' . $filename );
	}

	/**
	 * Register settings.
	 *
	 * @since    1.0.0
	 */
	public function secure_encrypted_form_page_init() {
		register_setting(
			'secure_encrypted_form_option_group',
			'secure_encrypted_form_option_name',
			array( $this, 'secure_encrypted_form_sanitize' )
		);

		add_settings_section(
			'secure_encrypted_form_setting_section',
			esc_attr__( 'Settings', 'secure-encrypted-form' ),
			array( $this, 'secure_encrypted_form_section_info' ),
			'secure-encrypted-form'
		);

		add_settings_field(
			'email',
			esc_attr__( 'Destination email', 'secure-encrypted-form' ),
			array( $this, 'email_callback' ),
			'secure-encrypted-form',
			'secure_encrypted_form_setting_section',
			array( 'description' => __( 'Complete this field with the email address that will get the secure email.', 'secure-encrypted-form' ) )
		);

		add_settings_field(
			'public_key',
			esc_attr__( 'OpenPGP Public key', 'secure-encrypted-form' ),
			array( $this, 'public_key_callback' ),
			'secure-encrypted-form',
			'secure_encrypted_form_setting_section',
			array( 'description' => __( 'Complete this field with your OpenPGP public key.', 'secure-encrypted-form' ) )
		);
	}

	/**
	 * Sanitize settings inputs
	 *
	 * @since    1.0.0
	 * @param callable $input Sanitize callback function.
	 */
	public function secure_encrypted_form_sanitize( $input ) {

		$sanitized_values = array();

		if ( isset( $input['email'] ) ) {
			$sanitized_values['email'] = sanitize_text_field( $input['email'] );
		}

		if ( isset( $input['public_key'] ) ) {
			$sanitized_values['public_key'] = esc_textarea( $input['public_key'] );
		}

		return $sanitized_values;
	}

	/**
	 * Print section text
	 *
	 * @since    1.0.0
	 */
	public function secure_encrypted_form_section_info() {

		print esc_html__( 'Enter your settings below', 'secure-encrypted-form' );

	}

	/**
	 * Email callback
	 *
	 * @since    1.0.0
	 * @param Array $args The extra arguments to add.
	 */
	public function email_callback( $args ) {

		printf(
			'<input class="regular-text" type="text" name="secure_encrypted_form_option_name[email]" id="email" value="%1s"><small>%2s</small>',
			isset( $this->options['email'] ) ? esc_attr( $this->options['email'] ) : '',
			esc_html( $args['description'] ),
		);

	}

	/**
	 * Publik key callback
	 *
	 * @since    1.0.0
	 * @param Array $args The extra arguments to add.
	 */
	public function public_key_callback( $args ) {

		printf(
			'<textarea class="large-text" rows="20" name="secure_encrypted_form_option_name[public_key]" id="public_key">%1s</textarea><small>%2s</small>',
			isset( $this->options['public_key'] ) ? esc_attr( $this->options['public_key'] ) : '',
			esc_html( $args['description'] ),
		);

	}

	/**
	 * Ajax function to process test form
	 *
	 * @since   1.0.0
	 */
	public function send_secure_test_form() {

		// This is a secure process to validate if this request comes from a valid source.
		check_ajax_referer( 'secure_form_nonce', 'security' );

		// Activate wp_mail errors.
		add_action( 'wp_mail_failed', array( $this, 'debug_wp_mail_failure' ) );

		$errors        = array();
		$data          = array();
		$message_field = '';

		// This is the email where you want to send the comments, defined in options.
		$to = get_option( 'secure_encrypted_form_option_name' )['email'];

		// Sanitize and define fields.
		if ( ! empty( $_POST['message'] ) ) {
			$message_field = json_decode( sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) );
		}

		// Message subject.
		$subject = esc_html__( 'Secure message [test]', 'secure-encrypted-form' );

		// Message body.
		$body  = esc_html__( 'From:', 'secure-encrypted-form' ) . ' John Doe<br>';
		$body .= 'Email: john@doe.com<br><br>';
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
			'Reply-To: John Doe <john@doe.com>',
		);

		$sent = wp_mail( $to, $subject, $body, $headers, $attachments );

		// Log action.
		if ( $sent ) {
			$data['success'] = true;
			$data['message'] = esc_html__( 'Success: secure encrypted message [test] sent.', 'secure-encrypted-form' );

			$this->logger->debug( 'Secure email [test] sent', array( 'to' => $to ) );

		} else {

			// This would be the wp_mail function failing to send the email. Eg the email on settings is wrong.
			$errors['server'] = true;
			$data['success']  = false;
			$data['errors']   = $errors;
			$data['message']  = esc_html__( 'Error E3: secure encrypted message could not be sent, see debug log please.', 'secure-encrypted-form' );

			// The error log is inyected by another function (debug_wp_mail_failure) to capture also the error code form wp_mail.
		}

		// Delete temp file (attachment).
		unlink( $filename );

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
		$this->logger->error( 'Secure email [test] not sent: ', $to );
		$this->logger->error( 'Internal error code E3' );
		$this->logger->error( 'wp_mail: ', $wp_error->errors );
	}

}
