<?php
/**
 * Provide a admin area log view for the plugin
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
			<h1>Secure Encrypted Form - Debug Log</h1>

			<p>
				<?php echo esc_html__( 'The debug log helps you to diagnose issues with the plugin. You can use the selector to see different logged days.', 'secure-encrypted-form' ); ?>
			</p>

			<?php if ( Secure_Encrypted_Form_Logger::MODE_OFF === Secure_Encrypted_Form_Logger::get_mode() ) : ?>
				<div class="notice notice-warning inline">
					<p>
						<?php
						printf(
							/* Translators: %1$s and %2$s are HTML a tags, please do not translate this parameter. */
							esc_html__( 'Logging is currently disabled, so no new entries are being recorded. You can enable it in the %1$splugin settings%2$s.', 'secure-encrypted-form' ),
							'<a href="' . esc_url( admin_url( 'admin.php?page=secure-encrypted-form' ) ) . '">',
							'</a>'
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( empty( $logs ) ) : ?>
				<p><?php echo esc_html__( 'There are no log files yet.', 'secure-encrypted-form' ); ?></p>
			<?php else : ?>
			<form method="post">
				<label for="debug_log_files"><?php echo esc_html__( 'Select a log file:', 'secure-encrypted-form' ); ?></label><br>
				<select name="debug_log_files" id="debug_log_files">
				<?php
				foreach ( $logs as $log ) {
					echo '<option value="' . esc_attr( $log ) . '"' . selected( $selected_log, $log, false ) . '>' . esc_html( $log ) . '</option>';
				}
				?>
				</select>
				<?php wp_nonce_field( 'sef-debug-logs' ); ?>
				<input class="button" type="submit" value="<?php echo esc_html__( 'View', 'secure-encrypted-form' ); ?>">
			</form>
			<?php endif; ?>

			<?php

			// Check if the user has selected a log file.
			if ( $selected_log ) {

				// Display the selected log file.
				echo '<h2>' . esc_html__( 'Viewing:', 'secure-encrypted-form' ) . ' ' . esc_html( $selected_log ) . '</h2>';
				echo '<div class="log-viewer">';
				echo '<pre>' . esc_html( $this->read_debug_log( $selected_log ) ) . '</pre>';
				echo '</div>';
			}
			?>

		</div>
		<!-- Sidebar -->
		<div>
			<?php require_once plugin_dir_path( __FILE__ ) . $this->plugin_name . '-admin-sidebar.php'; ?>
		</div>
	</div>
</div>
