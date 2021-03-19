<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver Onboarding Wizard Configuration.
 * Make appropriate changes to constants and methods, where applicable.
 *
 * @todo Set the config namespace.
 * @todo Check the to-dos of all constants and methods for more information.
 *
 * @package TheWebSolver\WooCommerce\Attribute\Onboarding\Class
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

namespace TheWebSolver\Woo\Attribute\Onboarding;

// namespace My_Plugin\My_Feature; // phpcs:ignore -- Namespace Example. Uncomment and use your own.

use TheWebSolver\Woo\Attribute\Installer;

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
	 * new Onboarding_Wizard( 'woocommerce', '', '4.5.0', self::CAPABILITY );
	 *
	 * // If ACF is dependency, then args can be:
	 * new Onboarding_Wizard( 'advanced-custom-fields', 'acf' );
	 * ```
	 * @todo Set necessary property values for the onboarding wizard.
	 */
	public function create_wizard() {
		// New shiny wizard creation.
		$onboarding_wizard = new Onboarding_Wizard( 'woocommerce' );
		$onboarding_wizard
		->set_prefix( self::PREFIX )
		->set_page( $this->get_page() )
		->set_asset_url( plugin_dir_url( __FILE__ ) . 'Assets' )
		->set_logo(
			array(
				'href'   => get_site_url( get_current_blog_id() ),
				'alt'    => 'The Web Solver Onboarding',
				'width'  => '135px',
				'height' => 'auto',
				'src'    => HZFEX_WOO_PAS_URL . 'Assets/Graphics/Options/separate-tabs.svg',
			)
		)
		->set_path( plugin_dir_path( __FILE__ ) ); // Used for locating template.

		return $onboarding_wizard;
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
	 * // Maybe you want to set same namespace for onboarding files Config.php and Wizard.php as in this file above.
	 * // If that's the case, then it will be more easy to work on.
	 *
	 * // For Now, lets assume it's different. So, first include the Onboarding Wizard main file like this:
	 * include_once '/path-to/tws-admin-onboarding.php';
	 *
	 * // Here, lets assume namespace is "My_Plugin\Onboarding" in Config.php and Wizard.php file.
	 * $onboarding = new TheWebSolver_Onboarding_Wizard( 'My_Plugin\Onboarding' );
	 * $onboarding->set_config();
	 *
	 * // Use it with register activation hook like this:
	 * register_activation_hook( __MY_MAIN_PLUGIN_FILE__, 'activate_function' );
	 * function activate_function() {
	 * \My_Plugin\Onboarding\Config::maybe_enable_wizard();
	 *
	 *  // or like this if namespace declarations are same.
	 *  // i.e. namespace is "My_Plugin\Onboarding" for all three files:
	 *  Config::maybe_enable_wizard();
	 * }
	 * ```
	 * @todo Use this with function registered at activation hook.
	 *       For more info: {@see function `register_activation_hook()`).
	 */
	public function maybe_enable_wizard() {
		/**
		 * WPHOOK: Filter -> enable/disable onboarding redirect after plugin activation.
		 *
		 * @param bool $redirect Whether to redirect or not.
		 *
		 * @var bool
		 *
		 * @since 1.0
		 */
		$onboard_redirect = apply_filters( self::PREFIX . '_onboarding_redirect', true );
		$is_new_install   = get_option( 'tws_woopas_installed_data', true );

		if ( $is_new_install && $onboard_redirect ) {
			set_transient( self::PREFIX . '_onboarding_redirect', 'yes', 30 );

			/**
			 * Use this option to conditionally show/hide admin notice (or anything else).
			 * It's will be updated to `complete` only after the last step of the onboarding wizard.
			 */
			update_option( self::PREFIX . '_onboarding_status', 'pending' );
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
		if ( is_admin() ) {
			$this->create_wizard();

			/**
			 * WPHOOK: Filter -> enable/disable onboarding redirect after plugin activation.
			 *
			 * @param bool $redirect Whether to redirect or not.
			 *
			 * @var bool
			 *
			 * @since 1.0
			 */
			$onboard_redirect = apply_filters( self::PREFIX . '_onboarding_redirect', true );

			// Start onboarding wizard if everything seems new and shiny!!!
			if (
				'yes' === get_transient( self::PREFIX . '_onboarding_redirect' ) &&
				true === current_user_can( self::CAPABILITY ) &&
				true === $onboard_redirect

				// REVIEW: Any additional checks can be done here.
				&& false === Installer::check_failed()
				) {
				add_action( 'admin_init', array( $this, 'start_onboarding_wizard' ) );
			}
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
			delete_transient( self::PREFIX . '_onboarding_redirect' );
			return;
		}

		// Once redirected, that's enough. Don't do it ever again.
		delete_transient( self::PREFIX . '_onboarding_redirect' );
		wp_safe_redirect( admin_url( 'index.php?page=' . $this->get_page() ) );
		exit;
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
		return self::PREFIX . '-onboarding-setup';
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
	 * Instantiates config if namespace matches.
	 *
	 * Singleton config class in this namespace.
	 *
	 * @param string $namespace This file namespace.
	 *
	 * @return Config|false
	 *
	 * @since 1.0
	 * @static
	 */
	public static function set( $namespace ) {
		static $config = false;
		if ( __NAMESPACE__ !== $namespace ) {
			return $config;
		}

		if ( ! is_a( $config, get_class() ) ) {
			$config = new self();
		}

		return $config;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {}
}
