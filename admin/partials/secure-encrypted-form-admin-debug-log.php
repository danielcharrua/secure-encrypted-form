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

			<form method="post">
				<label for="debug_log_files"><?php echo esc_html__( 'Select a log file:', 'secure-encrypted-form' ); ?></label><br>
				<select name="debug_log_files">
				<?php
				foreach ( $logs as $log ) {
					echo '<option value="' . esc_attr( $log ) . '">' . esc_html( $log ) . '</option>';
				}
				?>
				</select>
				<?php wp_nonce_field( 'sef-debug-logs' ); ?>
				<input class="button" type="submit" value="<?php echo esc_html__( 'View', 'secure-encrypted-form' ); ?>">
			</form>

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
