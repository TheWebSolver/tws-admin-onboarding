<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver WordPress Admin Onboarding Wizard.
 *
 * @todo Make changes to todo tags, where assigned.
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
		 * The prefix for onboarding wizard.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		private $prefix;

		/**
		 * The current user capability who can onboard wizard.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		private $capability;

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
		private $child_name = '';

		/**
		 * Onboarding wizard constructor.
		 *
		 * If `$src` and `$name` parameters are not provided or anything is wrong with the parameter, default child-class
		 * `Onboarding_Wizard` in file `Includes/Wizard.php` will be used.\
		 * Class `Onboarding_Wizard` in file `Includes/Wizard.php` is there as a boilerplate and can be used to create the onboarding wizard.\
		 * All needed abstract functions as already declared there. Make appropriate changes as it seems fit for your use case.
		 *
		 * @param string $namespace  Your Plugin unique namespace.
		 * @param string $prefix     Prefix for onboarding wizard. MUST BE UNIQUE AND MUST NOT BE CHANGED ONCE SET.
		 * @param string $capability The current user capability who can manage onboarding.
		 *                           Usually the highest level capability. Defaults to `manage_options` **(admin)**.
		 * @param string $src        (Optional) The child-class file source path.\
		 *                           **MUST HAVE SAME { @see @param _$namespace_ } DECLARED AT THE TOP OF THIS SOURCE FILE**.
		 * @param string $name       (Optional) The onboarding wizard child-class extending abstract class. Just the classname.\
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
		 * @todo Use the passed namespace at top of files "Config.php" and "Includes/Wizard.php".
		 */
		public function __construct( string $namespace, string $prefix, string $capability = 'manage_options', string $src = '', string $name = '' ) {
			$this->namespace  = $namespace;
			$this->prefix     = $prefix;
			$this->capability = $capability;
			$this->child_file = $src;
			$this->child_name = $name;

			// Include the web solver API abstraction class.
			include_once __DIR__ . '/thewebsolver.php';

			// Include core files.
			include_once __DIR__ . '/Config.php';
			include_once __DIR__ . '/Includes/Source/Onboarding.php';

			if ( is_wp_error( $this->config() ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				wp_die( $this->config()->get_error_message(), $this->config()->get_error_data() );
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
		 * Gets configuration instance.
		 *
		 * Config can only be instantiated if namespace matches with namesapce defined in config file.
		 * { @see @property TheWebSolver_Onboarding_Wizard::$namespace }
		 *
		 * @return object|WP_Error Instantiated config object, WP_Error if namespace mismatch.
		 *
		 * @since 1.0
		 */
		public function config() {
			$namespace = self::validate( $this->namespace, $this->capability, $this->prefix );

			if ( is_wp_error( $namespace ) ) {
				return $namespace;
			}

			$config   = '\\' . $namespace . '\\Config';
			$instance = call_user_func(
				array( $config, 'get' ),
				$namespace,
				$this->prefix,
				$this->capability,
				$this->child_file,
				$this->child_name
			);

			return $instance;
		}

		/**
		 * Validates namespace.
		 *
		 * This static helper method is created for DRY DEVELOPMENT.\
		 * This can validate namespace in this class as well as in config class.
		 *
		 * @param string $namespace   The onboarding wizard config namespace.
		 * @param string $cap         The current user capability.
		 * @param string $prefix      The onboarding wizard prefix.
		 * @param bool   $config_file Whether validation is from `Config.php` file or not. Default is `false`.
		 * @param string $config_ns   Namespace declared on `Config.php` file. No effect if `$config_file` is `false`.
		 *
		 * @return string|WP_Error Namespace if valid, `WP_Error` otherwise.
		 *
		 * @since 1.0
		 * @static
		 */
		public static function validate( string $namespace, string $cap, string $prefix, bool $config_file = false, string $config_ns = '' ) {
			// Trim beginning slashes from namespace, if any, to exact match namespace.
			$ns      = ltrim( $namespace, '\\' );
			$config  = '\\' . $ns . '\\Config';
			$dir     = ltrim( dirname( __FILE__ ), ABSPATH );
			$located = '';

			if ( 'myplugin-prefix' === $prefix || '' === $prefix ) {
				// Prefix errors.
				$prefix_title = __( 'Onboarding class prefix error', 'tws-onboarding' );
				$prefix_msg   = sprintf(
					'<h1>%1$s</h1><p>%2$s.</p><p>%3$s.</p>',
					$prefix_title,
					__( 'Use your plugin\'s same unique prefix passed when instantiating <code><b><em>TheWebSolver_Onboarding_Wizard</em></b></code> class to the onboarding wizard child-class private method <code><b><em>Onboarding_Wizard::config()</em></b></code> to get the config instance', 'tws-onboarding' ),
					__( 'Default prefix <b><em>"myplugin-prefix"</em></b> is being used', 'tws-onboarding' )
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

			if ( ! function_exists( 'wp_get_current_user' ) ) {
				include_once ABSPATH . 'wp-includes/pluggable.php';
			}

			$user_caps = wp_get_current_user()->allcaps;

			// Only show directory information if user has given capability.
			if ( isset( $user_caps[ $cap ] ) && $user_caps[ $cap ] ) {
				$located = sprintf( '%1$s <code><b><em>%2$s</em></b></code>', __( 'Files are located inside', 'tws-onboarding' ), $dir );
			}

			$allowed_html = array(
				'b'    => array(),
				'em'   => array(),
				'code' => array(),
			);

			// Case where namespace might be an empty string.
			if ( 0 === strlen( $ns ) ) {
				$notitle = __( 'Namespace not declared', 'tws-onboarding' );
				$nons    = __( 'Namespace is not declared for the Onboarding Wizard Configuration file.', 'tws-onboarding' );
				$nonote  = __( 'Use your plugin\'s unique namespace when instantiating <code><b><em>TheWebSolver_Onboarding_Wizard</em></b></code> and also declare the same namespace at the top of the <code><b><em>Config.php</em></b></code> and <code><b><em>Includes/Wizard.php</em></b></code> files.', 'tws-onboarding' );

				return new WP_Error(
					'namespace_not_declared',
					sprintf(
						'<h1>%1$s</h1><p>%2$s</p><p>%3$s</p><p>%4$s</p>',
						esc_html( $notitle ),
						esc_html( $nons ),
						wp_kses( $nonote, $allowed_html ),
						wp_kses( $located, $allowed_html )
					),
					esc_html( $notitle )
				);
			}

			$title   = __( 'Namespace Mismatch', 'tws-onboarding' );
			$message = __( 'Onboarding Config was instantiated with wrong namespace.', 'tws-onboarding' );
			$note    = __( 'Add same namespace that is passed when instantiating <code><b><em>TheWebSolver_Onboarding_Wizard</em></b></code> at top of the <code><b><em>Config.php</em></b></code> and <code><b><em>Includes/Wizard.php</em></b></code> files.', 'tws-onboarding' );
			$passed  = __( 'Namespace currently passed is', 'tws-onboarding' );

			$error = new WP_Error(
				'namespace_no_match',
				sprintf(
					'<h1>%1$s</h1><p>%2$s</p><p>%3$s</p><p>%4$s</p><hr><p>%5$s <code><b><em>%6$s</em></b></code></p>',
					esc_html( $title ),
					esc_html( $message ),
					wp_kses( $note, $allowed_html ),
					wp_kses( $located, $allowed_html ),
					esc_html( $passed ),
					esc_html( $namespace )
				),
				esc_html( $title )
			);

			// Check if validation is for this class or Config class.
			if ( false === $config_file ) {
				// Case where config file can't be called in given namespace.
				if ( ! is_callable( array( $config, 'get' ) ) ) {
					return $error;
				}
			} else {
				// Case where namespace of config file didn't match with given namespace.
				if ( $config_ns !== $ns ) {
					return $error;
				}
			}

			return $ns;
		}
	}
}
