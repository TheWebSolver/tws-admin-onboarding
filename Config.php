<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver Onboarding Wizard Configuration.
 * Make appropriate changes to constants and methods, where applicable.
 *
 * @todo Set the config namespace.
 * @todo Check the to-dos of all constants and methods for more information.
 *
 * @package TheWebSolver\Core\Admin\Onboarding\Class
 *
 * -----------------------------------
 * DEVELOPED-MAINTAINED-SUPPPORTED BY
 * -----------------------------------
 * ███║     ███╗   ████████████████
 * ███║     ███║   ═════════██████╗
 * ███║     ███║        ╔══█████═╝
 *  ████████████║      ╚═█████
 * ███║═════███║      █████╗
 * ███║     ███║    █████═╝
 * ███║     ███║   ████████████████╗
 * ╚═╝      ╚═╝    ═══════════════╝
 */

namespace My_Plugin\My_Feature; // phpcs:ignore -- Namespace Example. MUST USE YOUR OWN.

use stdClass;
use WP_Error;
use TheWebSolver\Core\Admin\Onboarding\Wizard;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Configuration class.
 */
final class Config {
	/**
	 * The onboarding prefixer.
	 *
	 * @var string
	 *
	 * @since 1.0
	 * @todo Prefix will be used all over onboarding setup. Make it unique.
	 *       Set the prefix once as needed when including in the plugin.
	 *       It will be used for WordPress hooks, options, transients, etc.
	 *       So, once set only change it if you are certain of the consequences.
	 */
	const PREFIX = 'tws-woopas';

	/**
	 * The user capability who can access onboarding.
	 *
	 * @var string Default is `manage_options` i.e. Admin Capability.
	 *
	 * @since 1.0
	 * @example usage
	 * ```
	 * // If dependency plugin is WooCommerce, then maybe use:
	 * const CAPABILITY = 'manage_woocommerce';
	 * // This filters the user cap and apply 'manage_woocommerce' capability to admin.
	 * // {@see @method `Wizard::init()`}
	 * ```
	 * @todo Only change capability if absolutely necessary.
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * The onboarding wizard child-class file path.
	 *
	 * @var string
	 */
	private $child_file;

	/**
	 * The onboarding wizard child-class name.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $child_name;

	/**
	 * Initializes onboarding wizard.
	 *
	 * @return object In this example, it will be `TWS_Myplugin_Wizard`.
	 *
	 * @since 1.0
	 * @example usage
	 * ```
	 * // If no dependency needed, then use like this:
	 * new Onboarding_Wizard();
	 *
	 * // If any dependency plugin needs to be installed on intro page, then use like this:
	 *
	 * // If WooCommerce is dependency, version to install is 4.5.0. Then args can be:
	 * new Onboarding_Wizard( 'woocommerce', '', '4.5.0', $this->get_capability() );
	 *
	 * // If ACF is dependency, then args can be:
	 * new Onboarding_Wizard( 'advanced-custom-fields', 'acf' );
	 * ```
	 * @todo Set necessary property values for the onboarding wizard.
	 */
	public function create_wizard() {
		// Prepare and instantiate external child-class, if valid.
		$class = new stdClass();
		if ( file_exists( $this->child_file ) && 0 < strlen( $this->child_name ) ) {
			// Include the child-class file.
			include_once $this->child_file;

			// Prepare classname using the same namespace as this (config) file.
			$child = '\\' . __NAMESPACE__ . '\\' . $this->child_name;
			$class = class_exists( $child ) ? new $child() : $class;
		}

		// Create onboarding wizard from external child-class, if instance of abstract class "Wizard".
		if ( $class instanceof Wizard ) {
			$onboarding = $class;
		} else {
			// New shiny wizard creation from internal child-class.
			$onboarding = new Onboarding_Wizard( 'woocommerce' );
		}

		$onboarding->init();

		return $onboarding;
	}

	/**
	 * Determines whether plugin onboarding should run or not after plugin activation.
	 *
	 * NOTE: WITHOUT USING THIS, ONBOARDING WIZARD WILL NOT START.
	 *
	 * By default a filter is created to enable/disable onboarding.
	 * Onboarding can then be turned off using this filter.
	 *
	 * Additional checks must be made as required.
	 * For eg. Saving an option during installation and checking whether that option exists.
	 * This way it will make sure that it's a clean install before redirecting to onboarding.
	 *
	 * @link https://developer.wordpress.org/reference/functions/register_activation_hook/
	 * @since 1.0
	 * @example usage
	 * ### Lets assume below code is in file `activate.php` in your plugin.
	 * ```
	 * namespace My_Plugin\Core;
	 * // Maybe you want to set namespace for onboarding files "Config.php" and "Includes/Wizard.php" same as in this file. i.e. "My_Plugin\Core".
	 * // If that's the case, then it will be more easy to work on.
	 *
	 * // For Now, lets assume it's different. So, first include the Onboarding Wizard main file like this:
	 * include_once 'my-plugin/path-to/tws-admin-onboarding.php';
	 * // Here, lets assume namespace is "My_Plugin\Onboarding" in "Config.php" and "Includes/Wizard.php" file. NOTE: Namespace must be same in these two files.
	 * $onboarding = new TheWebSolver_Onboarding_Wizard( 'My_Plugin\Onboarding' );
	 *
	 * // Use it with register activation hook like this:
	 * register_activation_hook( __MY_PLUGIN_MAIN_FILE__, 'activate_function' );
	 * function activate_function() {
	 * $onboarding->config()->enable_wizard();
	 * }
	 * ```
	 * @todo Use this with function registered at activation hook.
	 *       For more info: {@see function `register_activation_hook()`).
	 */
	public function enable_wizard() {
		/**
		 * WPHOOK: Filter -> enable/disable onboarding redirect after plugin activation.
		 *
		 * @param bool $redirect Whether to redirect or not.
		 *
		 * @var bool
		 *
		 * @since 1.0
		 * @example usage
		 * ```
		 * // Disable redirection after plugin activation.
		 * add_filter( 'hzfex_enable_onboarding_redirect', 'no_redirect', 10, 2 );
		 * function no_redirect( $enable, $prefix ) {
		 *  // Bail if not our onboarding wizard.
		 *  if ( 'my-prefix' !== $prefix ) {
		 *   return $enable;
		 *  }
		 *
		 *  return false;
		 * }
		 * ```
		 */
		$onboard_redirect = apply_filters( 'hzfex_enable_onboarding_redirect', true, $this->get_prefix() );
		$is_new_install   = get_option( 'tws_woopas_installed_data', true );

		if ( $is_new_install && $onboard_redirect ) {
			set_transient( $this->get_prefix() . '_onboarding_redirect', 'yes', 30 );

			/**
			 * Use this option to conditionally show/hide admin notice (or anything else).
			 * It's will be updated to `complete` only after the last step of the onboarding wizard.
			 */
			update_option( $this->get_prefix() . '_onboarding_status', 'pending' );
		}
	}

	/**
	 * Instantiates Onboarding Wizard class.
	 *
	 * Additional checks must be made as required.
	 * For eg. Check for WordPress and PHP versions, etc.
	 *
	 * @since 1.0
	 * @todo Any conditional check before starting wizard can be done here.
	 */
	public function start_onboarding() {
		// Only run on WordPress Admin.
		if ( ! is_admin() ) {
			return;
		}

		$this->create_wizard();

		/**
		 * WPHOOK: Filter -> enable/disable onboarding redirect after plugin activation.
		 *
		 * @param bool $redirect Whether to redirect or not.
		 *
		 * @var bool
		 *
		 * @since 1.0
		 * @example usage
		 * ```
		 * // Disable redirection after plugin activation.
		 * add_filter( 'hzfex_enable_onboarding_redirect', 'no_redirect', 10, 2 );
		 * function no_redirect( $enable, $prefix ) {
		 *  // Bail if not our onboarding wizard.
		 *  if ( 'my-prefix' !== $prefix ) {
		 *   return $enable;
		 *  }
		 *
		 *  return false;
		 * }
		 * ```
		 */
		$onboard_redirect = apply_filters( 'hzfex_enable_onboarding_redirect', true, $this->get_prefix() );

		/**
		 * WPHOOK: Filter -> additional check before starting onboarding wizard after plugin activation.
		 *
		 * @param string[] $check Must have all values as `true` (in string, not bool) to pass the check.
		 *
		 * @var string[]
		 *
		 * @since 1.0
		 * @example usage
		 * ```
		 * // Lets make some checks before onboarding redirection after plugin activation.
		 * add_filter( 'hzfex_onboarding_check_before_start', 'start_onboarding', 10, 2 );
		 * function start_onboarding( $check, $prefix ) {
		 *  // Bail if not our onboarding wizard.
		 *  if ( 'my-prefix' !== $prefix ) {
		 *   return $check;
		 *  }
		 *
		 *  // If PHP version less than or equal to "7.0", don't redirect onboarding.
		 *  if ( version_compare( phpversion(), '7.0', '<=' ) ) {
		 *   $check[] = 'false';
		 *  }
		 *
		 *  return $check;
		 * }
		 * ```
		 */
		$check = apply_filters( 'hzfex_onboarding_check_before_start', array( 'true' ), $this->get_prefix() );

		// Start onboarding wizard if everything seems new and shiny!!!
		if (
			'yes' === get_transient( $this->get_prefix() . '_onboarding_redirect' ) &&
			true === current_user_can( $this->get_capability() ) &&
			true === $onboard_redirect &&
			! in_array( 'false', $check, true )
			) {
			add_action( 'admin_init', array( $this, 'start_onboarding_wizard' ) );
		}
	}

	/**
	 * Handles redirection to onboarding wizzzaaaaardddd!!!!!.
	 *
	 * @see {@method `Config::start_onboarding()`}
	 * @since 1.0
	 */
	public function start_onboarding_wizard() {
		// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$current_page = isset( $_GET['page'] ) ? wp_unslash( $_GET['page'] ) : false;

		// Bail early on these events.
		if ( wp_doing_ajax() || is_network_admin() ) {
			return;
		}

		// Bail if on onboarding page or multiple-plugins activated at once.
		// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( $this->get_page() === $current_page || isset( $_GET['activate-multi'] ) ) {
			delete_transient( $this->get_prefix() . '_onboarding_redirect' );
			return;
		}

		// Once redirected, that's enough. Don't do it ever again.
		delete_transient( $this->get_prefix() . '_onboarding_redirect' );
		wp_safe_redirect( admin_url( 'index.php?page=' . $this->get_page() ) );
		exit;
	}

	/**
	 * Gets onboarding wizard prefix.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_prefix() {
		return self::PREFIX;
	}

	/**
	 * Gets user capability to run onboarding wizard.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_capability() {
		return self::CAPABILITY;
	}

	/**
	 * Creates and gets onboarding wizard page slug.
	 *
	 * @return string
	 *
	 * @since 1.0
	 * @example usage
	 *
	 * ```
	 * // To point to onboarding page, create URL like this.
	 * admin_url( 'admin.php?page=' . Config::get_page() );
	 * ```
	 * @todo Use this to point to the onboarding page slug, where applicable.
	 */
	public function get_page() {
		return $this->get_prefix() . '-onboarding-setup';
	}

	/**
	 * Gets onboarding root URL.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_url() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Gets onboarding root path.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_path() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Instantiates config if namespace matches.
	 *
	 * Singleton config class in this namespace.
	 *
	 * @param string $namespace This file namespace.
	 * @param string $src       (Optional) The child-class file source path.
	 * @param string $name      (optional) The onboarding wizard child-class extending abstract class.
	 *
	 * @return Config|void Config instance in this namespace, die with WP_Error msg if namespace not declared or did't match.
	 *
	 * @since 1.0
	 * @static
	 */
	public static function get( string $namespace, $src = '', string $name = '' ) {
		static $config = false;

		// Trim beginning slashes from namespace, if any, to exact match namespace.
		$namespace = ltrim( $namespace, '\\' );
		$error     = null;
		$file      = basename( __FILE__ );
		$dir       = dirname( __FILE__ );
		$noncmsg   = __( 'Declare valid namespace', 'tws-onboarding' );
		$matchmsg  = __( 'Add same namespace that is passed', 'tws-onboarding' );
		$message   = sprintf(
			'%1$s <b>%2$s</b>. %3$s <b>%4$s</b>.',
			__( 'when instantiating <b>TheWebSolver_Onboarding_Wizard</b> at top of the file', 'tws-onboarding' ),
			$file,
			__( 'File is located inside plugin\'s directory', 'tws-onboarding' ),
			$dir
		);

		// Die with not declared message. No cheating by passing empty string.
		if ( 0 === strlen( $namespace ) ) {
			$error = new WP_Error(
				'namespace_not_declared',
				__( 'Namespace is not declared for the Onboarding Wizard Configuration file.', 'tws-onboarding' ),
				__( 'Namespace not declared', 'tws-onboarding' )
			);
			wp_die(
				sprintf(
					'<h1>%1$s</h1><p>%2$s</p><p>%3$s %4$s</p>',
					esc_html( $error->get_error_data() ),
					esc_html( $error->get_error_message() ),
					esc_html( $noncmsg ),
					wp_kses_post( $message )
				),
				esc_html( $error->get_error_data() )
			);

			return $error;
		}

		// Die with no mach message.
		if ( __NAMESPACE__ !== $namespace ) {
			$error = new WP_Error(
				'namespace_no_match',
				__( 'Namespace did not match for the Onboarding Wizard Configuration file.', 'tws-onboarding' ),
				__( 'Namespace Mismatch', 'tws-onboarding' )
			);
			wp_die(
				sprintf(
					'<h1>%1$s</h1><p>%2$s</p><p>%3$s %4$s</p>',
					esc_html( $error->get_error_data() ),
					esc_html( $error->get_error_message() ),
					esc_html( $matchmsg ),
					wp_kses_post( $message )
				),
				esc_html( $error->get_error_data() )
			);

			return $error;
		}

		if ( ! is_a( $config, get_class() ) ) {
			$config = new self();

			// Prepare external child-class file, if given.
			$config->child_file = $src;

			// Set child-class name, if given, breaking namespace supplied.
			$child_name         = explode( '\\', $name );
			$config->child_name = array_pop( $child_name );
		}

		return $config;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {}
}
