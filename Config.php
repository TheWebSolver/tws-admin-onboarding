<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver WordPress Admin Onboarding Wizard Configuration.
 *
 * @todo Set the config namespace.
 * @todo Check all todo tags and make approriate changes where needed.
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

namespace My_Plugin\My_Feature; // phpcs:ignore -- Namespace Example. @todo MUST REPLACE AND USE YOUR OWN.

use stdClass;
use TheWebSolver_Onboarding_Wizard;
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
	 */
	private $prefix = 'myplugin-prefix';

	/**
	 * The user capability who can access onboarding.
	 *
	 * @var string Default is `manage_options` i.e. Admin Capability.
	 *
	 * @since 1.0
	 */
	private $capability = 'manage_options';

	/**
	 * The onboarding wizard child-class file path.
	 *
	 * @var string
	 *
	 * @since 1.0
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
	 * @return object The external child-class onboarding instance if valid, `Onboarding_Wizard` if not.
	 *
	 * @since 1.0
	 */
	public function onboarding() {
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
			$onboarding = (object) $class; // Typecasting to disable method call errors.

			// Pass current config object if method exists in external child class.
			if ( method_exists( $onboarding, 'set_config' ) && is_callable( array( $onboarding, 'set_config' ) ) ) {
				$onboarding->set_config( $this );
			}
		} else {
			// New shiny wizard creation from internal child-class.
			include_once __DIR__ . '/Includes/Wizard.php';
			$onboarding = new Onboarding_Wizard();
			$onboarding->set_config( $this );
		}

		$onboarding->init();

		return $onboarding;
	}

	/**
	 * Determines whether plugin onboarding should run or not after plugin activation.
	 *
	 * NOTE: WITHOUT USING THIS, ONBOARDING WIZARD WILL NOT REDIRECT AFTER PLUGIN ACTIVATION.
	 *
	 * By default a filter is created to enable/disable onboarding.
	 * Onboarding can then be turned off using this filter.
	 *
	 * Additional checks must be made as required.
	 * For eg. Saving an option during installation and checking whether that option exists.
	 * This way it will make sure that it's a clean install before enabling onboarding.
	 *
	 * @param string[] $check Validation before enabling onboarding during plugin activation.
	 *                        Must have all values as `true` *(in string, not bool)* to pass the check.
	 *
	 * @link https://developer.wordpress.org/reference/functions/register_activation_hook/
	 * @since 1.0
	 * @example usage
	 * ```
	 * // Let's assume we have main plugin file "myplugin.php"
	 *
	 * // First include the Onboarding Wizard main file like this:
	 * include_once 'my-plugin/path-to/tws-admin-onboarding.php';
	 *
	 * // Here, lets assume you want your onboarding namespace as "My_Plugin\Onboarding" and have already set it at top of "Config.php" and "Includes/Wizard.php" file.
	 *
	 * // NOTE: Namespace must be same in these two files. Default namespace is set as "My_Plugin\My_Feature" for "Config.php" and "Includes/Wizard.php" file. Replace it with "My_Plugin\Onboarding" for this example.
	 *
	 * // Lets instantiate the onboarding wizard.
	 * $onboarding = new TheWebSolver_Onboarding_Wizard( 'My_Plugin\Onboarding' );
	 *
	 * // Use it with register activation hook like this:
	 * register_activation_hook( __FILE__, 'activate_function' );
	 * function activate_function() {
	 *  // Check if plugin is already installed.
	 *  $old_install = get_option( 'myplugin_install_version', false );
	 *
	 *  if ( ! $old_install ) {
	 *   // if new install => enable onboarding.
	 *   $check[] = 'true';
	 *
	 *   // Set the plugin install version to "1.0".
	 *   update_option( 'myplugin_install_version', '1.0' );
	 *  } else {
	 *   //  There is now installed version "1.0" => disable onboarding.
	 *   $check[] = 'false';
	 *  }
	 *
	 *  // If PHP version less than or equal to "7.0" => disable onboarding.
	 *  if ( version_compare( phpversion(), '7.0', '<=' ) ) {
	 *   $check[] = 'false';
	 *  }
	 *
	 *  // Now onboarding will run on the basis of check parameter passed. This prevents enabling onboarding on each activation except first time and if version compare succeeds.
	 *  $onboarding->config()->enable_onboarding( $check );
	 * }
	 * ```
	 * @todo Use this with function registered at activation hook.
	 *       For more info: {@see function `register_activation_hook()`).
	 */
	public function enable_onboarding( array $check ) {
		/**
		 * WPHOOK: Filter -> enable/disable onboarding redirect after plugin activation.
		 *
		 * @param bool $redirect Whether to redirect or not.
		 * @var bool
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
		$redirect = apply_filters( 'hzfex_enable_onboarding_redirect', true, $this->get_prefix() );

		if ( $redirect && ! in_array( 'false', $check, true ) ) {
			set_transient( $this->get_prefix() . '_onboarding_redirect', 'yes', 30 );

			/**
			 * Use this option to conditionally show/hide admin notice (or anything else).
			 * It's will be updated to `complete` only at the last step of the onboarding wizard.
			 */
			update_option( $this->get_prefix() . '_onboarding_steps_status', 'pending' );
		}
	}

	/**
	 * Instantiates Onboarding Wizard class.
	 *
	 * Additional checks must be made as required.
	 * For eg. Check for WordPress and PHP versions, etc.
	 *
	 * @since 1.0
	 * @todo Use filter `hzfex_onboarding_check_before_redirect` for additional check before start.
	 */
	public function start_onboarding() {
		// Only run on WordPress Admin.
		if ( ! is_admin() ) {
			return;
		}

		$this->onboarding();

		/**
		 * WPHOOK: Filter -> enable/disable onboarding redirect after plugin activation.
		 *
		 * Same filter used during activation, for preventing any redirection bypass.
		 *
		 * @param bool $redirect Whether to redirect or not.
		 * @var bool
		 * @see {@method `Wizard::enable_onboarding()`}
		 * @since 1.0
		 */
		$redirect = apply_filters( 'hzfex_enable_onboarding_redirect', true, $this->get_prefix() );

		// Start onboarding wizard if everything seems new and shiny!!!
		if (
			'yes' === get_transient( $this->get_prefix() . '_onboarding_redirect' ) &&
			true === current_user_can( $this->get_capability() ) &&
			true === $redirect
			) {
			add_action( 'admin_init', array( $this, 'init' ) );
		}
	}

	/**
	 * Starts onboarding.
	 *
	 * Voila!!! We are now at onboarding intro page.
	 *
	 * @see {@method `Config::start_onboarding()`}
	 * @since 1.0
	 */
	public function init() {
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
		wp_safe_redirect( admin_url( 'admin.php?page=' . $this->get_page() ) );
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
		return $this->prefix;
	}

	/**
	 * Gets user capability to run onboarding wizard.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_capability() {
		return $this->capability;
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
	 * @param string $namespace  This file namespace. Prefix will be used all over onboarding setup.
	 *                           {@todo MUST BE A UNIQUE NAMESPACE FOR YOUR PLUGIN}
	 *                           It will be used for WordPress Hooks, Options, Transients, etc.
	 *                           So, once set only change it if you are certain of the consequences.
	 * @param string $prefix     Prefix for onboarding wizard. Only change once set if you know the consequences.
	 *                           {@todo MUST BE A UNIQUE PREFIX FOR YOUR PLUGIN}.
	 * @param string $capability The current user capability who can manage onboarding.
	 *                           {@todo CHANGE CAPABILITY ONLY IF ABSOLUTELY NECESSARY}.
	 *                           For e.g. If will be using WooCommerce later, or maybe installing WooCommerce
	 *                           as dependency plugin from within intro page of onboarding wizard,
	 *                           then maybe set it as `manage_woocommerce` (although not necessary).
	 *                           This filters the user cap and apply `manage_woocommerce` capability to `admin`
	 *                           even if `WooCommerce` not installed yet.
	 *                           {@see @method `TheWebSolver\Core\Admin\Onboarding\Wizard::init()`}.
	 * @param string $src        (Optional) The child-class file source path.
	 * @param string $name       (optional) The onboarding wizard child-class extending abstract class.
	 *
	 * @return Config|void Config instance in this namespace, die with WP_Error msg if namespace not declared or did't match.
	 *
	 * @since 1.0
	 * @static
	 */
	public static function get( string $namespace, string $prefix, string $capability = 'manage_options', $src = '', string $name = '' ) {
		static $config = false;

		$namespace = TheWebSolver_Onboarding_Wizard::validate( $namespace, $capability, $prefix, true, __NAMESPACE__ );

		// Don't let bypassing config namespace error if config is instantiated directly.
		if ( is_wp_error( $namespace ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die( $namespace->get_error_message(), $namespace->get_error_data() );
		}

		if ( ! is_a( $config, get_class() ) ) {
			$config = new self();

			// Set onboarding prefix.
			$config->prefix = $prefix;

			// Set onboarding capability.
			$config->capability = $capability;

			// Set external child-class file.
			$config->child_file = $src;

			// Set child-class name, breaking namespace supplied and just getting the class name.
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
