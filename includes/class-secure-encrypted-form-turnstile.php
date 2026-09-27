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
	 * The handle the widget script is registered under.
	 *
	 * @since    1.3.0
	 * @var      string
	 */
	const SCRIPT_HANDLE = 'cloudflare-turnstile';

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
	 * Ask the common optimisation plugins to leave the widget script alone.
	 *
	 * Turnstile looks for its own script tag in the page to read its
	 * configuration. Caching plugins that combine, minify or defer scripts break
	 * that: the tag disappears into a bundle, the widget never renders, and the
	 * form then refuses every submission for want of a token. The symptom looks
	 * like a plugin bug, so it is worth preventing rather than documenting.
	 *
	 * Both routes are used, because they cover different plugins: markup
	 * attributes, which several optimisers honour, and the exclusion filters the
	 * rest provide.
	 *
	 * @since    1.3.0
	 */
	public static function exclude_from_optimizers() {

		add_filter( 'script_loader_tag', array( __CLASS__, 'add_optimizer_attributes' ), 10, 2 );

		// SiteGround Speed Optimizer.
		add_filter( 'sgo_javascript_combine_exclude', array( __CLASS__, 'add_handle_to_list' ) );
		add_filter( 'sgo_js_minify_exclude', array( __CLASS__, 'add_handle_to_list' ) );
		add_filter( 'sgo_js_async_exclude', array( __CLASS__, 'add_handle_to_list' ) );
		add_filter( 'sgo_javascript_combine_excluded_external_paths', array( __CLASS__, 'add_url_to_list' ) );

		// LiteSpeed Cache.
		add_filter( 'litespeed_optimize_js_excludes', array( __CLASS__, 'add_url_to_list' ) );

		// WP Rocket.
		add_filter( 'rocket_exclude_js', array( __CLASS__, 'add_url_to_list' ) );
		add_filter( 'rocket_delay_js_exclusions', array( __CLASS__, 'add_url_to_list' ) );

		// Autoptimize, which takes a comma separated string instead of a list.
		add_filter( 'autoptimize_filter_js_exclude', array( __CLASS__, 'add_url_to_string_list' ) );
	}

	/**
	 * Mark the widget script so optimisers that read markup skip it.
	 *
	 * @since    1.3.0
	 * @param    string $tag       The script tag.
	 * @param    string $handle    The script handle.
	 * @return   string            The tag, marked when it is the widget script.
	 */
	public static function add_optimizer_attributes( $tag, $handle ) {

		if ( self::SCRIPT_HANDLE !== $handle ) {
			return $tag;
		}

		return str_replace(
			'<script ',
			'<script data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-noptimize="1" data-cfasync="false" ',
			$tag
		);
	}

	/**
	 * Add the widget script handle to an exclusion list.
	 *
	 * @since    1.3.0
	 * @param    mixed $excluded    The list an optimiser passes in.
	 * @return   array              The list with the handle added.
	 */
	public static function add_handle_to_list( $excluded ) {

		$excluded   = is_array( $excluded ) ? $excluded : array();
		$excluded[] = self::SCRIPT_HANDLE;

		return $excluded;
	}

	/**
	 * Add the widget script URL to an exclusion list.
	 *
	 * @since    1.3.0
	 * @param    mixed $excluded    The list an optimiser passes in.
	 * @return   array              The list with the URL added.
	 */
	public static function add_url_to_list( $excluded ) {

		$excluded   = is_array( $excluded ) ? $excluded : array();
		$excluded[] = self::SCRIPT_URL;

		return $excluded;
	}

	/**
	 * Add the widget script URL to a comma separated exclusion list.
	 *
	 * @since    1.3.0
	 * @param    mixed $excluded    The string an optimiser passes in.
	 * @return   string             The string with the URL added.
	 */
	public static function add_url_to_string_list( $excluded ) {

		$excluded = is_string( $excluded ) ? $excluded : '';

		return '' === $excluded ? self::SCRIPT_URL : $excluded . ',' . self::SCRIPT_URL;
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
				'Turnstile could not be reached, letting the submission through:',
				array( 'error' => $response->get_error_message() )
			);

			return true;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== (int) $code ) {
			$this->logger->error(
				'Turnstile answered an unexpected status, letting the submission through:',
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
				'Turnstile rejected the secret key, check the plugin settings. Letting the submission through:',
				array( 'codes' => $codes )
			);

			return true;
		}

		$this->logger->debug( 'Turnstile rejected a submission:', array( 'codes' => $codes ) );

		return false;
	}
}
