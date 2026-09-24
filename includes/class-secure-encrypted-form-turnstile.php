<?php
/**
 * Cloudflare Turnstile spam protection.
 *
 * @link       https://charrua.es
 * @since      1.3.0
 *
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/includes
 */

/**
 * Cloudflare Turnstile spam protection.
 *
 * Optional and disabled by default. When the site owner enables it and provides
 * both keys, the public form renders a Turnstile widget and the submission is
 * checked against Cloudflare before the email is sent.
 *
 * The visitor IP address is deliberately never sent to Cloudflare: the whole
 * point of this plugin is to keep sensitive submissions private.
 *
 * @since      1.3.0
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/includes
 * @author     Daniel Pereyra Costas <hola@charrua.es>
 */
class Secure_Encrypted_Form_Turnstile {

	/**
	 * The option holding the plugin settings.
	 *
	 * @since    1.3.0
	 * @var      string
	 */
	const SETTINGS_OPTION = 'secure_encrypted_form_option_name';

	/**
	 * The Cloudflare endpoint that validates a token.
	 *
	 * @since    1.3.0
	 * @var      string
	 */
	// phpcs:ignore PluginCheck.CodeAnalysis.Offloading.OffloadedContent -- Turnstile is a third party anti spam service; its endpoint cannot be self hosted and is disclosed in readme.txt.
	const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

	/**
	 * The Cloudflare script that renders the widget.
	 *
	 * @since    1.3.0
	 * @var      string
	 */
	// phpcs:ignore PluginCheck.CodeAnalysis.Offloading.OffloadedContent -- Cloudflare requires the widget script to be loaded from their domain; bundling it is not possible.
	const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

	/**
	 * The name of the field the widget adds to the form.
	 *
	 * @since    1.3.0
	 * @var      string
	 */
	const TOKEN_FIELD = 'cf-turnstile-response';

	/**
	 * The logger.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      Secure_Encrypted_Form_Logger    $logger    The logger.
	 */
	private $logger;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.3.0
	 * @param    Secure_Encrypted_Form_Logger $logger    The plugin logger.
	 */
	public function __construct( $logger = null ) {

		$this->logger = $logger instanceof Secure_Encrypted_Form_Logger ? $logger : new Secure_Encrypted_Form_Logger();
	}

	/**
	 * Read one setting.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @param    string $key    The settings key to read.
	 * @return   string         The stored value, or an empty string.
	 */
	private static function get_setting( $key ) {

		$options = get_option( self::SETTINGS_OPTION );

		if ( ! is_array( $options ) || ! isset( $options[ $key ] ) || ! is_string( $options[ $key ] ) ) {
			return '';
		}

		return trim( $options[ $key ] );
	}

	/**
	 * Get the site key, which is public and rendered in the page.
	 *
	 * @since    1.3.0
	 * @return   string    The site key, or an empty string.
	 */
	public static function get_site_key() {

		return self::get_setting( 'turnstile_site_key' );
	}

	/**
	 * Get the secret key, which is only ever sent to Cloudflare.
	 *
	 * @since    1.3.0
	 * @return   string    The secret key, or an empty string.
	 */
	public static function get_secret_key() {

		return self::get_setting( 'turnstile_secret_key' );
	}

	/**
	 * Check whether Turnstile should be used.
	 *
	 * Both keys are required: a half configured widget would block every
	 * submission, so it is treated as disabled.
	 *
	 * @since    1.3.0
	 * @return   bool    Whether the form should render and verify the widget.
	 */
	public static function is_enabled() {

		$options = get_option( self::SETTINGS_OPTION );

		if ( ! is_array( $options ) || empty( $options['turnstile_enabled'] ) ) {
			return false;
		}

		return '' !== self::get_site_key() && '' !== self::get_secret_key();
	}

	/**
	 * Build the markup for the widget.
	 *
	 * @since    1.3.0
	 * @return   string    The widget container, or an empty string when disabled.
	 */
	public static function get_widget_markup() {

		if ( ! self::is_enabled() ) {
			return '';
		}

		return sprintf(
			'<div class="form-group"><div class="cf-turnstile" data-sitekey="%s" data-retry="auto"></div></div>',
			esc_attr( self::get_site_key() )
		);
	}

	/**
	 * Validate a token against Cloudflare.
	 *
	 * When Cloudflare cannot be reached, or answers something unexpected, the
	 * submission is let through and the problem is logged: losing a legitimate
	 * message is worse than letting some spam in.
	 *
	 * A missing token is a different matter and is always rejected. Accepting
	 * submissions without one would let any bot skip the check by simply not
	 * sending the field.
	 *
	 * @since    1.3.0
	 * @param    string $token    The token the widget produced.
	 * @return   bool             Whether the submission may continue.
	 */
	public function verify( $token ) {

		if ( ! self::is_enabled() ) {
			return true;
		}

		if ( '' === trim( (string) $token ) ) {
			return false;
		}

		$response = wp_remote_post(
			self::VERIFY_URL,
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => self::get_secret_key(),
					'response' => $token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->logger->error(
				'Turnstile could not be reached, letting the submission through: ',
				array( 'error' => $response->get_error_message() )
			);

			return true;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== (int) $code ) {
			$this->logger->error(
				'Turnstile answered an unexpected status, letting the submission through: ',
				array( 'status' => $code )
			);

			return true;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) || ! isset( $body['success'] ) ) {
			$this->logger->error( 'Turnstile answered an unreadable body, letting the submission through.' );

			return true;
		}

		if ( true === $body['success'] ) {
			return true;
		}

		$codes = isset( $body['error-codes'] ) && is_array( $body['error-codes'] ) ? implode( ', ', $body['error-codes'] ) : '';

		// A bad secret key is the site owner's problem, not the visitor's, so it
		// is treated like an outage instead of blocking every message.
		if ( false !== strpos( $codes, 'invalid-input-secret' ) || false !== strpos( $codes, 'missing-input-secret' ) ) {
			$this->logger->error(
				'Turnstile rejected the secret key, check the plugin settings. Letting the submission through: ',
				array( 'codes' => $codes )
			);

			return true;
		}

		$this->logger->debug( 'Turnstile rejected a submission: ', array( 'codes' => $codes ) );

		return false;
	}
}
