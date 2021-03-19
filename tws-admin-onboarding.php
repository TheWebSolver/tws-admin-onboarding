<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver WordPress Admin Onboarding Wizard.
 *
 * @todo Make changes to todo tags, where assigned.
 *
 * @package TheWebSolver\Core\Admin\Onboarding
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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Onboarding Wizard.
 */
final class TheWebSolver_Onboarding_Wizard {
	/**
	 * The namespace for onboarding wizard.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $namespace;

	/**
	 * The path to this file's directory.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $path;

	/**
	 * The URL to this file's directory.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $url;

	/**
	 * Onboarding wizard constructor.
	 *
	 * @param string $namespace The Plugin namespace.
	 *
	 * @since 1.0
	 * @example usage
	 * ```
	 * // Set plugin namespace like this:
	 * $wizard = new TWS_Onboarding_Wizard( 'My_Plugin\My_Feature' );
	 *
	 * // In files `Config.php` and `Wizard.php`, add namesapce at top like this:
	 * namespace My_Plugin\My_Feature;
	 *  // code starts here.
	 * ```
	 * @todo Use the passed namespace for `Config.php` and `Wizard.php`.
	 */
	public function __Construct( $namespace ) {
		$this->namespace = $namespace;
		$this->url       = plugin_dir_url( __FILE__ );
		$this->path      = plugin_dir_path( __FILE__ );

		include_once __DIR__ . '/Config.php';
		include_once __DIR__ . '/Includes/Source/Onboarding.php';

		// Define default assets URL path.
		define( 'HZFEX_ONBOARDING_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'Assets/' );

		/**
		 * The child class that extends main onboarding class.
		 *
		 * @todo Extend abstract methods inside this file.
		 * {@see @method \TheWebSolver\Core\Admin\Onboarding\Wizard::set_steps()}
		 */
		include_once __DIR__ . '/Includes/Wizard.php';

		// WordPress Hook to start onboarding.
		if ( false !== $this->get_config() ) {
			add_action( 'init', array( $this->get_config(), 'start_onboarding' ) );
		}

		// Include the web solver API abstraction class.
		if ( ! class_exists( 'TheWebSolver' ) ) {
			include_once __DIR__ . '/thewebsolver.php';
		}
	}

	/**
	 * Gets the namespace to be set for `Config.php` and `Wizard.php` file.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Gets onboarding wizard url.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Gets onboarding wizard path.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Gets configuration instance.
	 *
	 * Config can only be instantiated if namespace matches with namesapce defined in config file.
	 * { @see @property TheWebSolver_Onboarding_Wizard::$namespace }
	 *
	 * @return object|false Instantiated config object, false if namespace didn't match.
	 *
	 * @since 1.0
	 */
	public function get_config() {
		return call_user_func( array( '\\' . $this->namespace . '\\Config', 'set' ), $this->namespace );
	}
}

