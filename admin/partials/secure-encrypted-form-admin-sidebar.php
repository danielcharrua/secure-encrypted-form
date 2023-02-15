<?php

/**
 * Provide an admin sidebar area view for the plugin
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

<div class="sidebar-panel">
	<h2>
		<?php echo esc_html__( 'Donations', 'secure-encrypted-form' ); ?>
	</h2>
	<p>
		<?php
		echo esc_html__(
			'If you find this plugin useful please consider donating to the author and keep working on the plugin. You can use the ⚡️ Lightning Network to send some sats.',
			'secure-encrypted-form'
		);
		?>
	</p>
	<p>
		<?php
		echo sprintf(
			/* Translators: %1$s and %2$s are HTML a tags, please do not translate this parameter. */
			esc_html__(
				'%1$sDonate here%2$s.',
				'secure-encrypted-form'
			),
			'<a href="' . esc_url( 'https://charrua.es/donaciones' ) . '">',
			'</a>',
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
		'<strong>',
		'</strong>',
	);
	?>
	<p>
		<?php
		echo sprintf(
			/* Translators: %1$s and %2$s are HTML a tags, please do not translate this parameter. */
			esc_html__(
				'%1$sRate plugin%2$s.',
				'secure-encrypted-form'
			),
			'<a href="' . esc_url( 'https://wordpress.org/plugins/secure-encrypted-form' ) . '">',
			'</a>',
		);
		?>
	</p>
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
		'<strong>',
		'</strong>',
		'<a href="' . esc_url( 'https://charrua.es/' ) . '">charrua.es</a>',
	);
	?>
</div>
