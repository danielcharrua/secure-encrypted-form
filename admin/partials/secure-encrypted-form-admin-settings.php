<?php
/**
 * Provide a admin area settings view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://charrua.es
 * @since      1.0.0
 *
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/admin/partials
 */

?>

<div class="wrap secure-form-wrapper">
	<div class="page-grid">
		<!-- Main content -->
		<div class="dashboard-main">
			<h1>Secure Encrypted Form</h1>
			<p>
				<?php echo esc_html__( 'The Secure Encrypted Form helps you to include a secure form in your website trought a shortcode. The message that is sent to you is encrypted with your public PGP key. This ensures that only you with the private key can decrypt the message.', 'secure-encrypted-form' ); ?>
			</p>
			<?php settings_errors(); ?>

			<h2>
				<?php echo esc_html__( 'Usage', 'secure-encrypted-form' ); ?>
			</h2>
			<p>
				<?php
				echo sprintf(
					/* Translators: %1$s and %2$s are HTML code tags. */
					esc_html__(
						'Insert shortcode in any page using %1$s[secure-encrypted-form]%2$s.',
						'secure-encrypted-form'
					),
					'<code>',
					'</code>'
				);
				?>
			</p>

			<form method="post" action="options.php" class="secure-form-admin">
				<?php
				settings_fields( 'secure_encrypted_form_option_group' );
				do_settings_sections( 'secure-encrypted-form' );
				submit_button();
				?>
			</form>

			<h2>
				<?php echo esc_html__( 'Having problems?', 'secure-encrypted-form' ); ?>
			</h2>
			<p>
				<?php echo esc_html__( 'If you are not sure if your website is sending emails correctly, you can use this button to test and send an email. Have in mind that your server is in charge of sending emails, maybe you need to install a plugin for using SMTP. Also, always save settings before sending test emails.', 'secure-encrypted-form' ); ?>
			</p>
			<form method="post" id="secure-form-test">
				<input type="submit" name="submit" class="button" value="<?php echo esc_attr__( 'Send test email', 'secure-encrypted-form' ); ?>">
			</form>
		</div>
		<!-- Sidebar -->
		<div>
			<?php require_once plugin_dir_path( __FILE__ ) . $this->plugin_name . '-admin-sidebar.php'; ?>
		</div>
	</div>
</div>
