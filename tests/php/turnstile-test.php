<?php
/**
 * Tests for the Turnstile verification logic.
 *
 * Run with: composer test:turnstile
 *
 * These use hand written stubs of the handful of WordPress functions the class
 * touches, so they run without a WordPress installation. They exist because the
 * interesting branches are the ones that are painful to reproduce by hand: what
 * happens when Cloudflare is down, slow, or answering nonsense.
 *
 * @package Secure_Encrypted_Form
 */

$GLOBALS['sef_options']  = array();
$GLOBALS['sef_response'] = null;
$GLOBALS['sef_requests'] = array();
$GLOBALS['sef_logged']   = array();

/**
 * Stub of get_option().
 *
 * @param string $name    Option name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function get_option( $name, $default = false ) {
	return array_key_exists( $name, $GLOBALS['sef_options'] ) ? $GLOBALS['sef_options'][ $name ] : $default;
}

/**
 * Stub of wp_remote_post() returning whatever the test queued.
 *
 * @param string $url  Endpoint.
 * @param array  $args Request arguments.
 * @return array|WP_Error
 */
function wp_remote_post( $url, $args = array() ) {
	$GLOBALS['sef_requests'][] = array(
		'url'  => $url,
		'args' => $args,
	);

	return $GLOBALS['sef_response'];
}

/**
 * Stub of wp_remote_retrieve_response_code().
 *
 * @param array $response Response.
 * @return int
 */
function wp_remote_retrieve_response_code( $response ) {
	return isset( $response['response']['code'] ) ? $response['response']['code'] : 0;
}

/**
 * Stub of wp_remote_retrieve_body().
 *
 * @param array $response Response.
 * @return string
 */
function wp_remote_retrieve_body( $response ) {
	return isset( $response['body'] ) ? $response['body'] : '';
}

/**
 * Stub of is_wp_error().
 *
 * @param mixed $thing Value to check.
 * @return bool
 */
function is_wp_error( $thing ) {
	return $thing instanceof WP_Error;
}

/**
 * Stub of esc_attr().
 *
 * @param string $text Text to escape.
 * @return string
 */
function esc_attr( $text ) {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

/**
 * Minimal stand in for WP_Error.
 */
class WP_Error {

	/**
	 * The error message.
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Constructor.
	 *
	 * @param string $message The error message.
	 */
	public function __construct( $message ) {
		$this->message = $message;
	}

	/**
	 * Returns the error message.
	 *
	 * @return string
	 */
	public function get_error_message() {
		return $this->message;
	}
}

/**
 * Records what the plugin would have logged.
 */
class Secure_Encrypted_Form_Logger {

	/**
	 * Records an error.
	 *
	 * @param string $message The message.
	 * @param array  $context The context.
	 */
	public function error( $message, $context = array() ) {
		$GLOBALS['sef_logged'][] = array( 'error', $message, $context );
	}

	/**
	 * Records a debug entry.
	 *
	 * @param string $message The message.
	 * @param array  $context The context.
	 */
	public function debug( $message, $context = array() ) {
		$GLOBALS['sef_logged'][] = array( 'debug', $message, $context );
	}
}

require __DIR__ . '/../../includes/class-secure-encrypted-form-turnstile.php';

$pass = 0;
$fail = 0;

/**
 * Tiny assertion helper.
 *
 * @param string $label The test name.
 * @param bool   $ok    Whether it passed.
 */
function check( $label, $ok ) {
	global $pass, $fail;

	if ( $ok ) {
		++$pass;
		echo "PASS  $label\n";
	} else {
		++$fail;
		echo "FAIL  $label\n";
	}
}

/**
 * Puts the plugin into a known configuration.
 *
 * @param array $overrides Settings to apply.
 */
function configure( $overrides = array() ) {
	$GLOBALS['sef_options']['secure_encrypted_form_option_name'] = array_merge(
		array(
			'turnstile_enabled'    => 1,
			'turnstile_site_key'   => '1x00000000000000000000AA',
			'turnstile_secret_key' => '1x0000000000000000000000000000000AA',
		),
		$overrides
	);

	$GLOBALS['sef_requests'] = array();
	$GLOBALS['sef_logged']   = array();
}

/**
 * Builds a fake Cloudflare answer.
 *
 * @param array $body The decoded body to return.
 * @param int   $code The HTTP status code.
 * @return array
 */
function cloudflare_says( $body, $code = 200 ) {
	return array(
		'response' => array( 'code' => $code ),
		'body'     => wp_json_encode_stub( $body ),
	);
}

/**
 * json_encode wrapper kept separate so the intent is obvious.
 *
 * @param mixed $value Value to encode.
 * @return string
 */
function wp_json_encode_stub( $value ) {
	return json_encode( $value ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
}

// --- Disabled by default.

$GLOBALS['sef_options'] = array();
check( 'disabled when there are no settings at all', ! Secure_Encrypted_Form_Turnstile::is_enabled() );

configure( array( 'turnstile_enabled' => 0 ) );
check( 'disabled when the checkbox is off', ! Secure_Encrypted_Form_Turnstile::is_enabled() );

configure( array( 'turnstile_site_key' => '' ) );
check( 'disabled when the site key is missing', ! Secure_Encrypted_Form_Turnstile::is_enabled() );

configure( array( 'turnstile_secret_key' => '' ) );
check( 'disabled when the secret key is missing', ! Secure_Encrypted_Form_Turnstile::is_enabled() );

configure();
check( 'enabled when the checkbox is on and both keys are set', Secure_Encrypted_Form_Turnstile::is_enabled() );

// --- Widget markup.

configure( array( 'turnstile_enabled' => 0 ) );
check( 'no widget markup when disabled', '' === Secure_Encrypted_Form_Turnstile::get_widget_markup() );

configure();
$markup = Secure_Encrypted_Form_Turnstile::get_widget_markup();
check( 'widget carries the site key', str_contains( $markup, 'data-sitekey="1x00000000000000000000AA"' ) );
check( 'widget never carries the secret key', ! str_contains( $markup, '1x0000000000000000000000000000000AA' ) );

// --- Verification.

configure( array( 'turnstile_enabled' => 0 ) );
$turnstile = new Secure_Encrypted_Form_Turnstile( new Secure_Encrypted_Form_Logger() );
check( 'everything passes when Turnstile is off', $turnstile->verify( '' ) );

configure();
$turnstile = new Secure_Encrypted_Form_Turnstile( new Secure_Encrypted_Form_Logger() );
check( 'a missing token is rejected', ! $turnstile->verify( '' ) );
check( 'a missing token does not call Cloudflare', array() === $GLOBALS['sef_requests'] );

configure();
$GLOBALS['sef_response'] = cloudflare_says( array( 'success' => true ) );
$turnstile               = new Secure_Encrypted_Form_Turnstile( new Secure_Encrypted_Form_Logger() );
check( 'a valid token passes', $turnstile->verify( 'a-token' ) );
check( 'the secret key is sent to Cloudflare', '1x0000000000000000000000000000000AA' === $GLOBALS['sef_requests'][0]['args']['body']['secret'] );
check( 'the visitor IP is never sent', ! isset( $GLOBALS['sef_requests'][0]['args']['body']['remoteip'] ) );

configure();
$GLOBALS['sef_response'] = cloudflare_says( array( 'success' => false, 'error-codes' => array( 'invalid-input-response' ) ) );
$turnstile               = new Secure_Encrypted_Form_Turnstile( new Secure_Encrypted_Form_Logger() );
check( 'a rejected token is blocked', ! $turnstile->verify( 'a-token' ) );

// --- Fail open: a Cloudflare problem must never cost a legitimate message.

configure();
$GLOBALS['sef_response'] = new WP_Error( 'http_request_failed', 'Connection timed out' );
$turnstile               = new Secure_Encrypted_Form_Turnstile( new Secure_Encrypted_Form_Logger() );
check( 'an unreachable Cloudflare lets the message through', $turnstile->verify( 'a-token' ) );
check( 'and the outage is logged as an error', 'error' === $GLOBALS['sef_logged'][0][0] );

configure();
$GLOBALS['sef_response'] = cloudflare_says( array( 'success' => true ), 503 );
$turnstile               = new Secure_Encrypted_Form_Turnstile( new Secure_Encrypted_Form_Logger() );
check( 'a 503 lets the message through', $turnstile->verify( 'a-token' ) );

configure();
$GLOBALS['sef_response'] = array(
	'response' => array( 'code' => 200 ),
	'body'     => 'not json at all',
);
$turnstile = new Secure_Encrypted_Form_Turnstile( new Secure_Encrypted_Form_Logger() );
check( 'an unreadable answer lets the message through', $turnstile->verify( 'a-token' ) );

configure();
$GLOBALS['sef_response'] = cloudflare_says( array( 'success' => false, 'error-codes' => array( 'invalid-input-secret' ) ) );
$turnstile               = new Secure_Encrypted_Form_Turnstile( new Secure_Encrypted_Form_Logger() );
check( 'a wrong secret key does not block visitors', $turnstile->verify( 'a-token' ) );
check( 'and it is logged as an error for the site owner', 'error' === $GLOBALS['sef_logged'][0][0] );

echo "\n$pass passed, $fail failed\n";

exit( $fail > 0 ? 1 : 0 );
