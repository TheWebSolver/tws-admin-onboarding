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
	 * // In files "Config.php" and "Includes/Wizard.php", add namesapce at top like this:
	 * namespace My_Plugin\My_Feature;
	 *  // code starts here.
	 * ```
	 * @todo Use the passed namespace for "Config.php" and "Includes/Wizard.php".
	 */
	public function __construct( $namespace ) {
		$this->namespace = $namespace;

		include_once __DIR__ . '/Config.php';
		include_once __DIR__ . '/Includes/Source/Onboarding.php';

		/**
		 * The child class that extends main onboarding class.
		 *
		 * @todo Extend abstract methods inside this file.
		 */
		include_once __DIR__ . '/Includes/Wizard.php';

		// WordPress Hook to start onboarding.
		if ( false !== $this->config() ) {
			add_action( 'init', array( $this->config(), 'start_onboarding' ) );
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
	 * Gets configuration instance.
	 *
	 * Config can only be instantiated if namespace matches with namesapce defined in config file.
	 * { @see @property TheWebSolver_Onboarding_Wizard::$namespace }
	 *
	 * @return object|false Instantiated config object, false if namespace didn't match.
	 *
	 * @since 1.0
	 */
	public function config() {
		return call_user_func( array( '\\' . $this->namespace . '\\Config', 'get' ), $this->namespace );
	}
}

