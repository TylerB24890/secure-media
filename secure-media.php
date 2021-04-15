<?php
/**
 * Plugin Name:       Secure Media
 * Plugin URI:        https://github.com/10up/secure-media
 * Description:       Store private media securely in WordPress
 * Version:           1.0.5
 * Requires at least:
 * Requires PHP:      5.6
 * Author:            10up
 * Author URI:        https://10up.com
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       secure-media
 *
 * Work in this plugin is derived from https://github.com/humanmade/S3-Uploads
 *
 * @package           secure-media
 */

namespace SecureMedia;

use WP_CLI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'SM_URL', plugin_dir_url( __FILE__ ) );
define( 'SM_PATH', plugin_dir_path( __FILE__ ) );
define( 'SM_VERSION', '1.0.5' );

/**
 * Authenticate Secure Media using a pre-authorized server IAM Role
 * or .credentials file
 *
 * @see https://docs.aws.amazon.com/IAM/latest/UserGuide/id_roles_use_switch-role-ec2.html
 */
define( 'SM_SERVER_AUTH', true );

/**
 * Allow the retreival of KMS encrypted S3 assets
 * Passes extra headers with S3 requests
 *
 * @see https://docs.aws.amazon.com/AmazonS3/latest/API/API_HeadObject.html
 * @see https://docs.aws.amazon.com/AmazonS3/latest/userguide/ServerSideEncryptionCustomerKeys.html
 */
define( 'SM_SERVER_ENCRYPTION', true );

require_once __DIR__ . '/inc/utils.php';

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	/**
	 * PSR-4-ish autoloading
	 */
	spl_autoload_register(
		function( $class ) {
				// project-specific namespace prefix.
				$prefix = 'SecureMedia\\';

				// base directory for the namespace prefix.
				$base_dir = __DIR__ . '/inc/classes/';

				// does the class use the namespace prefix?
				$len = strlen( $prefix );

			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				return;
			}

				$relative_class = substr( $class, $len );

				$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

				// if the file exists, require it.
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	);
}

// Define a constant if we're network activated to allow plugin to respond accordingly.
$network_activated = Utils\is_network_activated( plugin_basename( __FILE__ ) );

define( 'SM_IS_NETWORK', (bool) $network_activated );

require_once __DIR__ . '/inc/settings.php';

Settings\setup();

SecureMedia::factory();

/**
 * WP CLI Commands
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'secure-media', __NAMESPACE__ . '\Command' );
}
