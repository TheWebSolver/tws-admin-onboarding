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

/**
 * Onboarding namespace.
 *
 * @todo MUST REPLACE AND USE OWN NAMESPACE.
 */
namespace My_Plugin\My_Feature;

use stdClass;
use WP_Error;
use TheWebSolver\Core\Admin\Onboarding\Wizard;
use TheWebSolver\Core\Admin\Onboarding\Form;

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
	private $prefix = 'thewebsolver';

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
	 * Onboarding form handler.
	 *
	 * @var Form
	 *
	 * @since 1.1
	 */
	public $form;

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
			/**
			 * External onboarding wizard child-class.
			 *
			 * @var \TheWebSolver\Core\Admin\Onboarding\Wizard
			 * */
			$onboarding = $class;
			$onboarding->set_config( $this );
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
	 * Onboarding can then be turned off using this filter.\
	 * FILTER IS USEFUL FOR END-USERS TO ENABLE/DISABLE ONBOARDING WIZARD.
	 *
	 * Additional checks must be made as required.
	 * For eg. Saving an option during installation and checking whether that option exists.
	 * This way it will make sure that it's a clean install before enabling onboarding.
	 *
	 * @param string[] $check Validation before enabling onboarding during plugin activation.
	 *                        Must have all values as `true` *(in string, not bool)* to pass the check.
	 *
	 * @see Onboarding::activate()
	 * @link https://developer.wordpress.org/reference/functions/register_activation_hook/
	 * @since 1.0
	 * @todo Use this with function registered at activation hook.
	 *       For more info: {@see register_activation_hook()).
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
	 * This is hooked to WordPress `init` action.
	 *
	 * @since 1.0
	 * @since 1.1 Added form handler class.
	 */
	public function start_onboarding() {
		// Only run on WordPress Admin.
		if ( ! is_admin() ) {
			return;
		}

		$this->onboarding();

		include_once __DIR__ . '/Includes/Source/Form.php';
		$this->form = new Form( $this->prefix, $this->get_path() . 'templates/' );

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

		// Start onboarding wizard if everything seems OKAYYYY!!!
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
		// phpcs:disable WordPress.Security.NonceVerification
		$get             = wp_unslash( $_GET );
		$current_page    = isset( $get['page'] ) ? $get['page'] : false;
		$multi_activated = isset( $get['activate-multi'] );
		// phpcs:enable WordPress.Security.NonceVerification

		// Bail early on these events.
		if ( wp_doing_ajax() || is_network_admin() ) {
			return;
		}

		// Bail if on onboarding page or multiple-plugins activated at once.
		if ( $this->get_page() === $current_page || $multi_activated ) {
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
	 * If `$src` and `$name` is given, then onboarding will be instantiated from that classname, if valid.
	 * {@see @method `Config::onboarding()`}.
	 *
	 * @param string $prefix     Prefix for onboarding wizard. Only change once set if you know the consequences.
	 *                           It will be used for WordPress Hooks, Options, Transients, etc.
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
	public static function get( string $prefix, string $capability = 'manage_options', $src = '', string $name = '' ) {
		static $config = false;

		$namespace = self::validate( $capability, $prefix );

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

			// Set external child-class name.
			$config->child_name = $name;

			// Include the web solver API abstraction class.
			include_once __DIR__ . '/thewebsolver.php';

			// Include the main onboarding abstract class.
			include_once __DIR__ . '/Includes/Source/Onboarding.php';

			// WordPress Hook to start onboarding.
			add_action( 'init', array( $config, 'start_onboarding' ) );
		}

		return $config;
	}

	/**
	 * Validates namespace and prefix.
	 *
	 * @param string $cap    The current user capability.
	 * @param string $prefix The onboarding wizard prefix.
	 *
	 * @return string|WP_Error Namespace if valid, `WP_Error` otherwise.
	 *
	 * @since 1.0
	 * @static
	 */
	private static function validate( string $cap, string $prefix ) {
		// Trim beginning slashes from namespace, if any, to exact match namespace.
		$ns      = __NAMESPACE__;
		$dir     = ltrim( dirname( __FILE__ ), ABSPATH ) . '/';
		$located = '';
		$default = 'My_Plugin\\My_Feature';

		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include_once ABSPATH . 'wp-includes/pluggable.php';
		}

		$user_caps = wp_get_current_user()->allcaps;

		// Only show directory information if user has given capability.
		if ( isset( $user_caps[ $cap ] ) && $user_caps[ $cap ] ) {
			$located = sprintf( '%1$s <code><b><em>%2$s</em></b></code>', __( 'Files are located inside directory:', 'tws-onboarding' ), $dir );
		}

		$allowed_html = array(
			'b'    => array(),
			'em'   => array(),
			'code' => array(),
		);

		if ( 'thewebsolver' === $prefix || '' === $prefix ) {
			// Prefix errors.
			$prefix_title = __( 'Onboarding class prefix error', 'tws-onboarding' );
			$prefix_msg   = sprintf(
				'<h1>%1$s</h1><p>%2$s.</p><p>%3$s.</p><p>%4$s</p>',
				$prefix_title,
				__( 'Use your plugin\'s unique prefix for <code><b><em>Config::get()</em></b></code> to get the config instance', 'tws-onboarding' ),
				__( 'Default prefix <b><em>"thewebsolver"</em></b> is being used', 'tws-onboarding' ),
				wp_kses( $located, $allowed_html )
			);

			return new WP_Error(
				'prefix_mismatch',
				wp_kses(
					$prefix_msg,
					array(
						'h1'   => array(),
						'p'    => array(),
						'b'    => array(),
						'em'   => array(),
						'code' => array(),
					)
				),
				esc_html( $prefix_title )
			);
		}

		$note = __( 'Set unique namespace to instantiate <code><b><em>Config::get()</em></b></code> and declare the same namespace at the top of the <code><b><em>Config.php</em></b></code> and <code><b><em>Includes/Wizard.php</em></b></code> files.', 'tws-onboarding' );

		// Case where namespace not declared.
		if ( 0 === strlen( __NAMESPACE__ ) ) {
			$notitle = __( 'Namespace not declared', 'tws-onboarding' );
			$nons    = __( 'Onboarding Config was instantiated without namespace.', 'tws-onboarding' );

			return new WP_Error(
				'namespace_not_declared',
				sprintf(
					'<h1>%1$s</h1><p>%2$s</p><p>%3$s</p><p>%4$s</p>',
					esc_html( $notitle ),
					esc_html( $nons ),
					wp_kses( $note, $allowed_html ),
					wp_kses( $located, $allowed_html )
				),
				esc_html( $notitle )
			);
		}

		// Case where default namespace is being used.
		if ( __NAMESPACE__ === $default ) {
			$title   = __( 'Namespace Not Unique', 'tws-onboarding' );
			$message = __( 'Onboarding Config was instantiated with default namespace.', 'tws-onboarding' );
			$passed  = __( 'Change this default namespace:', 'tws-onboarding' );

			return new WP_Error(
				'namespace_no_match',
				sprintf(
					'<h1>%1$s</h1><p>%2$s</p><p>%3$s</p><p>%4$s</p><hr><p>%5$s <code><b><em>%6$s</em></b></code></p>',
					esc_html( $title ),
					esc_html( $message ),
					wp_kses( $note, $allowed_html ),
					wp_kses( $located, $allowed_html ),
					esc_html( $passed ),
					esc_html( $default )
				),
				esc_html( $title )
			);
		}

		return $ns;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {}
}
