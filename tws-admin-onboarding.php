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

if ( ! class_exists( 'TheWebSolver_Onboarding_Wizard' ) ) {
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
		private $child_name = '';

		/**
		 * Onboarding wizard constructor.
		 *
		 * If `$src` and `$name` parameters are not provided or anything is wrong with the parameter, default child-class
		 * `Onboarding_Wizard` in file `Includes/Wizard.php` will be used.\
		 * Class `Onboarding_Wizard` in file `Includes/Wizard.php` is there as a boilerplate and can be used to create the onboarding wizard. All needed abstract functions as already declared there. Make appropriate changes as it seems fit for your use case.
		 *
		 * @param string $namespace Your Plugin unique namespace.
		 * @param string $src       (Optional) The child-class file source path.\
		 *                          **MUST HAVE SAME _$namespace_ DECLARED AT THE TOP OF THIS SOURCE FILE**.
		 * @param string $name      (Optional) The onboarding wizard child-class extending abstract class. Just the classname.\
		 *                          No need to add namespace before class as it will be handled by config.
		 *
		 * @since 1.0
		 * @example usage
		 * ```
		 * // Set plugin namespace like this:
		 * $wizard = new TheWebSolver_Onboarding_Wizard( 'My_Plugin\My_Feature' );
		 *
		 * // In files "Config.php" and "Includes/Wizard.php", add namesapce at top like this:
		 * namespace My_Plugin\My_Feature;
		 *  // code starts here.
		 * ```
		 * @todo Use the passed namespace for "Config.php" and "Includes/Wizard.php".
		 */
		public function __construct( string $namespace, string $src = '', string $name = '' ) {
			$this->namespace  = $namespace;
			$this->child_file = $src;
			$this->child_name = $name;

			include_once __DIR__ . '/Config.php';
			include_once __DIR__ . '/Includes/Source/Onboarding.php';

			/**
			 * The child class that extends main onboarding class.
			 *
			 * @todo Extend abstract methods inside this file.
			 */
			include_once __DIR__ . '/Includes/Wizard.php';

			// WordPress Hook to start onboarding.
			if ( ! is_wp_error( $this->config() ) ) {
				add_action( 'init', array( $this->config(), 'start_onboarding' ) );
			}

			// Include the web solver API abstraction class.
			include_once __DIR__ . '/thewebsolver.php';
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
			// Trim beginning slashes from namespace, if any, to exact match namespace.
			$ns      = ltrim( $this->namespace, '\\' );
			$config  = '\\' . $ns . '\\Config';
			$dir     = dirname( __FILE__ );
			$title   = __( 'Namespace Mismatch', 'tws-onboarding' );
			$nons    = __( 'Namespace is not declared for the Onboarding Wizard Configuration file.', 'tws-onboarding' );
			$message = __( 'Onboarding Config was instantiated with wrong namespace.', 'tws-onboarding' );
			$nonote  = __( 'Use plugin\'s unique namespace when instantiating <b>TheWebSolver_Onboarding_Wizard</b> and also declare the same namespace at the top of the <b>Config.php</b> and <b>Includes/Wizard.php</b> files. <br>Files are located inside', 'tws-onboarding' );
			$note    = __( 'Add same namespace that is passed when instantiating <b>TheWebSolver_Onboarding_Wizard</b> at top of the <b>Config.php</b> and <b>Includes/Wizard.php</b> files. <br>Files are located inside', 'tws-onboarding' );
			$set     = __( 'Namespace currently passed is', 'tws-onboarding' );

			if ( 0 === strlen( $ns ) ) {
				wp_die(
					sprintf(
						'<h1>%1$s</h1><p>%2$s</p><p>%3$s <b>%4$s</b>.</p>',
						esc_html( $title ),
						esc_html( $nons ),
						wp_kses(
							$nonote,
							array(
								'b'  => array(),
								'br' => array(),
							)
						),
						esc_html( $dir ),
					),
					esc_html( $title )
				);
			}

			if ( ! is_callable( array( $config, 'get' ) ) ) {
				wp_die(
					sprintf(
						'<h1>%1$s</h1><p>%2$s</p><p>%3$s <b>%4$s</b>.</p><hr><p>%5$s <b>%6$s</b>.</p>',
						esc_html( $title ),
						esc_html( $message ),
						wp_kses(
							$note,
							array(
								'b'  => array(),
								'br' => array(),
							)
						),
						esc_html( $dir ),
						esc_html( $set ),
						esc_html( $this->namespace )
					),
					esc_html( $title )
				);
			}

			return call_user_func( array( $config, 'get' ), $ns, $this->child_file, $this->child_name );
		}
	}
}
