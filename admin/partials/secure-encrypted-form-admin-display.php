<?php
/**
 * Provide a admin area view for the plugin
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

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap secure-form-wrapper">
	<div class="page-grid">
		<div class="dashboard_main">
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
				do_settings_sections( 'secure-encrypted-form-admin' );
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

			<h2>
				<?php echo esc_html__( 'Support', 'secure-encrypted-form' ); ?>
			</h2>
			<p>
				<?php echo esc_html__( 'If you need to debug please use the button below to dowload the plugin log.', 'secure-encrypted-form' ); ?>
			</p>
			<button class="button"><?php echo esc_html__( 'Download debug log', 'secure-encrypted-form' ); ?></button>
		</div>
		<div>
			<div class="sidebar-panel">
				<h2>
					<?php echo esc_html__( 'Donations', 'secure-encrypted-form' ); ?>
				</h2>
				<p>
					<?php
					echo esc_html__(
						'If you find this plugin usefull please consider donating to the author and keep working on the plugin. You can use the ⚡️ Lightning Network to send some sats.',
						'secure-encrypted-form'
					);
					?>
				</p>
				<a href="lightning:LNURL1DP68GURN8GHJ7MRWW3UXYMM59E3K7MF09EMK2MRV944KUMMHDCHKCMN4WFK8QTMYV9HXJETVWP3K7UM5V9ES4MRMQW">
					<img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'images/qr-lnurl.svg' ); ?>" alt="Donation QR link" />
				</a>
				<p>
					<?php
					echo sprintf(
						/* Translators: %1$s is donation address, please do not translate this parameter. */
						esc_html__(
							'You can use the QR above or our Lightning Address: %1$s',
							'secure-encrypted-form'
						),
						'<b>danielpcostas@getalby.com</b>',
					);
					?>
				</p>
			</div>
			<div class="sidebar-panel">
				<h2>
					<?php echo esc_html__( 'Rate this plugin', 'secure-encrypted-form' ); ?>
				</h2>
				<?php
				echo sprintf(
					/* Translators: %1$s and %2$s are HTML bold tags. */
					esc_html__(
						'Help us spread the word %1$sby giving Secure Encrypted Form a 5-star rating (⭐️⭐️⭐️⭐️⭐️) on WordPress.org%2$s. Thanks for your support and we look forward to bringing you more awesome features.',
						'secure-encrypted-form'
					),
					'<b>',
					'</b>',
				);
				?>
			</div>
			<div class="sidebar-panel">
				<h2>
					<?php echo esc_html__( 'Need WordPress custom work?', 'secure-encrypted-form' ); ?>
				</h2>
				<?php
				echo sprintf(
					/* Translators: %1$s and %2$s are HTML bold tags; %3$s is charrua.es website link. */
					esc_html__(
						'This plugin is created and supported by %1$sCharrúa ⚡️ - Building Smarter Websites%2$s. If you need some custom WordPress work please contact us at %3$s.',
						'secure-encrypted-form'
					),
					'<b>',
					'</b>',
					'<a href="' . esc_url( 'https://charrua.es/' ) . '">charrua.es</a>',
				);
				?>
			</div>
		</div>
	</div>
</div>
