<?php //phpcs:ignore WordPress.NamingConventions
/**
 * Plugin Name: The Web Solver Onboarding Wizard
 * Description: Boilerplate for enabling The Web Solver WordPress Admin Onboarding Wizard.
 * Version:     1.0
 * Author:      TheWebSolver
 *
 * @package TheWebSolver\Core\Admin\Onboarding\Class
 * @todo    Configured to be tested as a WordPress plugin.
 *          Must create own plugin file and delete this file
 *          after implementing codes here inside own plugin file.
 * TODO:    Make changes to codes with {@todo} tags, where applicable.
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

/**
 * Boilerplate plugin for The Web Solver WordPress Admin Onboarding Wizard.
 */
final class Onboarding {
	/**
	 * Onboarding wizard prefix.
	 *
	 * @var string
	 * @todo Prefix for onboarding wizard. DO NOT CHANGE IT ONCE SET.\
	 *       It will be used for WordPress Hooks, Options, Transients, etc.\
	 *       MUST BE A UNIQUE PREFIX FOR YOUR PLUGIN.
	 */
	public $prefix = 'thewebsolver';

	/**
	 * Onboarding Wizard Config.
	 *
	 * @var Config
	 */
	public $config;

	/**
	 * Starts Onboarding.
	 *
	 * @return Onboarding
	 */
	public static function start() {
		static $onboarding;
		if ( ! is_a( $onboarding, get_class() ) ) {
			$onboarding = new self();
		}
		return $onboarding;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {
		$this->config();
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		/**
		 * If all onboarding steps are not completed, show admin notice.
		 *
		 * At last step of onboarding, $status => 'complete'.
		 *
		 * @var string
		 *
		 * @todo Need to perform additional checks before showing notice
		 *       Such as show notice on plugins, themes and dashboard pages only.
		 */
		$status = get_option( $this->prefix . '_onboarding_steps_status' );
		if ( 'pending' === $status ) {
			add_action( 'admin_notices', array( $this, 'onboarding_notice' ) );
		}
	}

	/**
	 * Instantiates onboarding config.
	 */
	private function config() {
		// Onboarding config file path.
		include_once __DIR__ . '/Config.php';
		$config = array( '\\' . __NAMESPACE__ . '\\Config', 'get' );

		// Only call config if it is on the same namespace.
		if ( is_callable( $config ) ) {
			$this->config = call_user_func( $config, $this->prefix );
		}
	}

	/**
	 * Sets onboarding notice if not completed.
	 */
	public function onboarding_notice() {
		$msg = sprintf(
			'<p><b>%1$s</b> - %2$s.</p><p><a href="%3$s" class="button-primary">%4$s</a></p>',
			__( 'Namaste! from The Web Solver Onboarding Wizard', 'tws-woopas' ),
			__( 'Let us help you quickly setup the plugin with our onboarding wizard', 'tws-woopas' ),
			admin_url( 'admin.php?page=' . $this->config->get_page() ),
			__( 'Run the Wizard Now', 'tws-woopas' )
		);

		echo '<div class="notice notice-info">' . wp_kses_post( $msg ) . '</div>';
	}

	/**
	 * Performs task on plugin activation.
	 *
	 * @todo Configured with example codes. Make changes as needed.
	 */
	public function activate() {
		// Check if plugin is already installed.
		$old_install = get_option( $this->prefix . '_install_version', false );

		if ( ! $old_install ) {
			// if new install => enable onboarding.
			$check[] = 'true';

			// Set the plugin install version to "1.0".
			update_option( $this->prefix . '_install_version', '1.0' );
		} else {
			// There is now installed version "1.0" => disable onboarding.
			$check[] = 'false';
		}

		// If PHP version less than or equal to "7.0" => disable onboarding.
		if ( version_compare( phpversion(), '7.0', '<=' ) ) {
			$check[] = 'false';
		}

		// Now onboarding will run on the basis of check parameter passed.
		// If this is first activation or PHP < 7.0 => redirect to onboarding page.
		// Lets also verify if config has been instantiated.
		if ( is_object( $this->config ) ) {
			$this->config->enable_onboarding( $check );
		}
	}

	/**
	 * Performs task on plugin deactivation.
	 *
	 * @todo Configured to delete onboarding options on plugin deactivation.\
	 *       Cane be safely deleted for production.
	 */
	public function deactivate() {
		// Onboarding options.
		delete_option( $this->prefix . '_onboarding_steps_status' );
		delete_option( $this->prefix . '_onboarding_dependency_status' );
		delete_option( $this->prefix . '_onboarding_dependency_name' );
		delete_option( $this->prefix . '_install_version' );

		// Onboarding transitents.
		delete_transient( $this->prefix . '_onboarding_redirect' );
	}
}

Onboarding::start();
