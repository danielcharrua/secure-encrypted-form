<?php
/**
 * The logging functionality of the plugin.
 *
 * @link       https://charrua.es
 * @since      1.2.0
 *
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/includes
 */

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;

/**
 * The logging functionality of the plugin.
 *
 * Writes the diagnostic log to a directory inside the uploads folder. Because that
 * folder is normally web accessible, the directory name carries a random suffix and
 * is protected with server configuration files, so log files cannot be reached from
 * the browser by guessing their path.
 *
 * @since      1.2.0
 * @package    Secure_Encrypted_Form
 * @subpackage Secure_Encrypted_Form/includes
 * @author     Daniel Pereyra Costas <hola@charrua.es>
 */
class Secure_Encrypted_Form_Logger {

	/**
	 * The option holding the plugin settings.
	 *
	 * @since    1.2.0
	 * @var      string
	 */
	const SETTINGS_OPTION = 'secure_encrypted_form_option_name';

	/**
	 * The option holding the randomized log directory name.
	 *
	 * @since    1.2.0
	 * @var      string
	 */
	const DIRNAME_OPTION = 'secure_encrypted_form_log_dirname';

	/**
	 * The option flagging that the pre 1.2.0 log directory was already handled.
	 *
	 * @since    1.2.0
	 * @var      string
	 */
	const MIGRATED_OPTION = 'secure_encrypted_form_logs_migrated';

	/**
	 * The log directory name used by versions prior to 1.2.0.
	 *
	 * @since    1.2.0
	 * @var      string
	 */
	const LEGACY_DIRNAME = 'secure-encrypted-form';

	/**
	 * Logging disabled.
	 *
	 * @since    1.2.0
	 * @var      string
	 */
	const MODE_OFF = 'off';

	/**
	 * Log errors only. This is the default.
	 *
	 * @since    1.2.0
	 * @var      string
	 */
	const MODE_ERRORS = 'errors';

	/**
	 * Log errors and successful deliveries.
	 *
	 * @since    1.2.0
	 * @var      string
	 */
	const MODE_DEBUG = 'debug';

	/**
	 * The configured logging mode.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      string    $mode    One of the MODE_* constants.
	 */
	private $mode;

	/**
	 * The Monolog logger, created on the first write.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      Logger|null    $logger    The logger.
	 */
	private $logger = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.2.0
	 */
	public function __construct() {

		$this->mode = self::get_mode();

	}

	/**
	 * Get the configured logging mode.
	 *
	 * Installations upgrading from an earlier version have no stored preference, so
	 * they keep logging errors, which is what they had before.
	 *
	 * @since    1.2.0
	 * @return   string    One of the MODE_* constants.
	 */
	public static function get_mode() {

		$options = get_option( self::SETTINGS_OPTION );

		if ( is_array( $options ) && isset( $options['logging'] ) && in_array( $options['logging'], self::get_modes(), true ) ) {
			return $options['logging'];
		}

		return self::MODE_ERRORS;
	}

	/**
	 * Get the available logging modes.
	 *
	 * @since    1.2.0
	 * @return   array    The valid values for the logging setting.
	 */
	public static function get_modes() {

		return array( self::MODE_OFF, self::MODE_ERRORS, self::MODE_DEBUG );
	}

	/**
	 * Get the name of the directory holding the log files.
	 *
	 * The random suffix keeps the log files from being reachable by guessing their
	 * URL on servers that ignore the .htaccess file, such as nginx.
	 *
	 * @since    1.2.0
	 * @return   string    The directory name, relative to the uploads folder.
	 */
	public static function get_log_dirname() {

		$dirname = get_option( self::DIRNAME_OPTION );

		if ( is_string( $dirname ) && '' !== $dirname ) {
			return $dirname;
		}

		$dirname = self::LEGACY_DIRNAME . '-' . wp_generate_password( 12, false, false );

		update_option( self::DIRNAME_OPTION, $dirname, false );

		return $dirname;
	}

	/**
	 * Get the absolute path of the directory holding the log files.
	 *
	 * @since    1.2.0
	 * @return   string    The absolute path, without a trailing slash.
	 */
	public static function get_log_path() {

		$upload_dir = wp_upload_dir();

		return trailingslashit( $upload_dir['basedir'] ) . self::get_log_dirname();
	}

	/**
	 * Move the log directory used before 1.2.0 to its protected location.
	 *
	 * Logs written by earlier versions live in a directory whose name is the plugin
	 * slug, so they can be downloaded by anyone who guesses the URL. Moving them
	 * preserves the history while making the old paths stop resolving.
	 *
	 * @since    1.2.0
	 */
	public static function maybe_migrate_legacy_directory() {

		if ( get_option( self::MIGRATED_OPTION ) ) {
			return;
		}

		update_option( self::MIGRATED_OPTION, true, false );

		$upload_dir = wp_upload_dir();
		$legacy     = trailingslashit( $upload_dir['basedir'] ) . self::LEGACY_DIRNAME;
		$target     = self::get_log_path();

		if ( $legacy === $target || ! is_dir( $legacy ) ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors -- A failed rename is handled below.
		if ( ! is_dir( $target ) && @rename( $legacy, $target ) ) {
			self::protect_directory( $target );
			return;
		}

		// The directory could not be moved, so protect the old logs where they are.
		self::protect_directory( $legacy );
	}

	/**
	 * Create the log directory and block direct web access to it.
	 *
	 * @since    1.2.0
	 * @param    string $path    The absolute path of the directory to protect.
	 * @return   bool            Whether the directory exists and is writable.
	 */
	public static function protect_directory( $path ) {

		if ( ! is_dir( $path ) && ! wp_mkdir_p( $path ) ) {
			return false;
		}

		$guards = array(
			'index.php'  => "<?php\n// Silence is golden.\n",
			'.htaccess'  => "# Deny direct access to the plugin log files.\n<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tOrder allow,deny\n\tDeny from all\n</IfModule>\n",
			'web.config' => "<configuration>\n\t<system.webServer>\n\t\t<authorization>\n\t\t\t<deny users=\"*\" />\n\t\t</authorization>\n\t</system.webServer>\n</configuration>\n",
		);

		foreach ( $guards as $filename => $contents ) {
			if ( ! file_exists( $path . '/' . $filename ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				file_put_contents( $path . '/' . $filename, $contents );
			}
		}

		return wp_is_writable( $path );
	}

	/**
	 * Get the log files, newest first.
	 *
	 * @since    1.2.0
	 * @return   array    The log file names.
	 */
	public static function get_log_files() {

		$path = self::get_log_path();

		if ( ! is_dir( $path ) ) {
			return array();
		}

		$files = glob( $path . '/*.log' );

		if ( ! is_array( $files ) ) {
			return array();
		}

		$files = array_map( 'basename', $files );

		rsort( $files );

		return $files;
	}

	/**
	 * Get the contents of a log file.
	 *
	 * @since    1.2.0
	 * @param    string $filename    The name of the log file.
	 * @return   string              The file contents, or an empty string if it is not a log file.
	 */
	public static function read_log_file( $filename ) {

		$filename = basename( $filename );

		// Only serve files the plugin itself wrote.
		if ( ! in_array( $filename, self::get_log_files(), true ) ) {
			return '';
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_get_contents
		$contents = file_get_contents( self::get_log_path() . '/' . $filename );

		return false === $contents ? '' : $contents;
	}

	/**
	 * Delete the log directory and its contents.
	 *
	 * @since    1.2.0
	 */
	public static function delete_logs() {

		$path = self::get_log_path();

		if ( ! is_dir( $path ) ) {
			return;
		}

		// scandir() is used instead of glob() because the directory also holds the
		// dot files that block web access to it.
		$files = scandir( $path );

		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				if ( '.' !== $file && '..' !== $file && is_file( $path . '/' . $file ) ) {
					wp_delete_file( $path . '/' . $file );
				}
			}
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors -- Nothing to do if the directory cannot be removed.
		@rmdir( $path );
	}

	/**
	 * Check whether messages of a given level are being logged.
	 *
	 * @since    1.2.0
	 * @param    string $level    Either 'error' or 'debug'.
	 * @return   bool             Whether the message should be written.
	 */
	public function is_enabled( $level ) {

		if ( self::MODE_OFF === $this->mode ) {
			return false;
		}

		return 'debug' !== $level || self::MODE_DEBUG === $this->mode;
	}

	/**
	 * Log an error.
	 *
	 * @since    1.2.0
	 * @param    string $message    The message to log.
	 * @param    array  $context    Extra data to log alongside the message.
	 */
	public function error( $message, $context = array() ) {

		if ( ! $this->is_enabled( 'error' ) ) {
			return;
		}

		$logger = $this->get_logger();

		if ( $logger ) {
			$logger->error( $message, $context );
		}
	}

	/**
	 * Log a debug message.
	 *
	 * @since    1.2.0
	 * @param    string $message    The message to log.
	 * @param    array  $context    Extra data to log alongside the message.
	 */
	public function debug( $message, $context = array() ) {

		if ( ! $this->is_enabled( 'debug' ) ) {
			return;
		}

		$logger = $this->get_logger();

		if ( $logger ) {
			$logger->debug( $message, $context );
		}
	}

	/**
	 * Initialize the Monolog logger.
	 *
	 * The directory is only created when something is actually going to be written
	 * to it, so installations with logging disabled leave no traces in the uploads
	 * folder.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @return   Logger|null    The logger, or null if the directory is not writable.
	 */
	private function get_logger() {

		if ( $this->logger instanceof Logger ) {
			return $this->logger;
		}

		$path = self::get_log_path();

		if ( ! self::protect_directory( $path ) ) {
			return null;
		}

		// The default date format is "Y-m-d\TH:i:sP".
		$date_format = 'Y-m-d\TH:i:s';

		// the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
		// we now change the default output format according to our needs.
		$output = "[%datetime%] %level_name%: %message% %context%\n";

		// finally, create a formatter.
		$formatter = new LineFormatter( $output, $date_format );

		// Create a handler.
		$rotating_file = new RotatingFileHandler( $path . '/log.log', 7 );
		$rotating_file->setFormatter( $formatter );

		// bind it to a logger object.
		$this->logger = new Logger( 'plugin-log' );
		$this->logger->pushHandler( $rotating_file );

		return $this->logger;
	}

}
