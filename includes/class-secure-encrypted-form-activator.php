<?php

/**
 * Fired during plugin activation
 *
 * @link       https://charrua.es
 * @since      1.0.0
 *
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/includes
 * @author     Daniel Pereyra Costas <hola@charrua.es>
 */
class Secure_Encrypted_Form_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

	}

	/**
	 * Check & define plugin options.
	 *
	 * On plugin activation, if we dont find the options we initialize them. As they
	 * are arrays and we check them later in code, we need to define those indexes.
	 *
	 * @since    1.0.1
	 */
	public static function define_plugin_options() {

		$plugin_options = array(
			'email'      => '',
			'public_key' => '',
		);

		if ( ! get_option( 'secure_encrypted_form_option_name' ) ) {
			add_option( 'secure_encrypted_form_option_name', $plugin_options );
		}

	}

}
