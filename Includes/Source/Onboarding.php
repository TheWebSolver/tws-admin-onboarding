<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver Onboarding Wizard.
 * Handles installation of dependency plugin at introduction page.
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

namespace TheWebSolver\Core\Admin\Onboarding;

use TheWebSolver;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Setup wizard class.
 *
 * Handles installation of dependency plugin at introduction page.
 *
 * @class TheWebSolver\Core\Admin\Onboarding\Wizard
 */
abstract class Wizard {
	/**
	 * Onboarding prefixer.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $prefix;

	/**
	 * The path to the onboarding root.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $path;

	/**
	 * Onboarding page slug.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $page;

	/**
	 * Onboarding assets URL.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $url;

	/**
	 * HTML head title.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $title;

	/**
	 * All steps.
	 *
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $steps = array();

	/**
	 * Current step.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $step = '';

	/**
	 * Actions to be executed after the HTTP response has completed.
	 *
	 * @var array
	 */
	private $deferred_actions = array();

	/**
	 * Dependency plugin already installed or not.
	 *
	 * @var bool True to ignore dependency.
	 *
	 * @since 1.0
	 */
	protected $is_installed = true;

	/**
	 * Dependency plugin already active or not.
	 *
	 * @var bool True to ignore dependency.
	 *
	 * @since 1.0
	 */
	protected $is_active = true;

	/**
	 * Dependency plugin slug/directory name.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $slug = '';

	/**
	 * Dependency plugin name.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $name = '';

	/**
	 * Dependency plugin filename.
	 *
	 * No need to set it if same as slug { @see @property Wizard::$slug }.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $filename = '';

	/**
	 * Dependency plugin version to install.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $version = 'latest';

	/**
	 * The user capability who can onboard.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	public $capability = 'manage_options';

	/**
	 * Recommended plugins.
	 *
	 * Recommended step is where the recommended
	 * plugins will be installed and activated.
	 *
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $recommended = array();

	/**
	 * The registered page hook suffix.
	 *
	 * @var string|false
	 *
	 * @since 1.0
	 */
	public $hook_suffix;

	/**
	 * Plugin Logo.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $logo;

	/**
	 * Reset onboarding wizard options.
	 *
	 * All keys for options reset are:
	 * * `dependency_name`
	 * * `dependency_status`
	 * * `recommended_status`
	 * * `recommended_checked_status`
	 *
	 * @var bool[]
	 *
	 * @since 1.0
	 */
	protected $reset = array();

	/**
	 * Onboarding constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		// Everything happens at `init`. Always call `init` after initializing class!!!
	}

	/**
	 * Sets dependency plugin args.
	 *
	 * If any plugin is required i.e. if this plugin is dependent on any other plugin,
	 * or this plugin is like an add-on/extension of the dependent plugin,
	 * Then it can be installed from onboarding first step (introduction).
	 * Pass the required `param` and everthing will be handled automatically.
	 *
	 * The dependency plugin status will be saved with Options API
	 * with key {`$this->prefix . '_onboarding_dependency_status'`}.
	 * So it is not possible to change it once set after plugin activation.
	 * To change dependency `param`, you need to reset key by:
	 * * deleting above option key with `delete_option()` at plugin deactivation/uninstall hook & deactivating/reinstalling plugin.
	 * * setting `$this->reset['dependency_name'] = true` & `$this->reset['dependency_status'] = true` and visiting `ready` step. To know more: {@see @method `Wizard::reset()`}.
	 * * manually deleting above key by any other means (maybe directly from database).
	 *
	 * Following properties are to be set in this method.
	 * * @param string `$slug`     The plugin's slug on WordPress repository (aka directory name).
	 * * @param string `$filename` The plugin's main file name.
	 *                             Only needed if different than `$slug`.
	 *                             Don't include the extension `.php`.
	 * * @param string `$version`  The plugin's version to install. Useful if PHP and/or WordPress
	 *                             not compatible with plugin's latest version. Defaults to `latest`.
	 *
	 * @since 1.0
	 * @example usage
	 * ```
	 * namespace My_Plugin\My_Feature;
	 * use TheWebSolver\Core\Admin\Onboarding\Wizard;
	 *
	 * // Lets assume our child-class is `Onboarding_Wizard` in above namespace.
	 * class Onboarding_Wizard extends Wizard {
	 *  protected function set_dependency() {
	 *   $this->slug     = 'woocommerce';
	 *   $this->filename = 'woocommerce'; // Not needed as it is same as slug. Included as an example.
	 *   $this->version  = '4.5.0'; // Not needed if latest to install. Can be: '4.5.0', '4.0.0' etc.
	 *  }
	 * }
	 * ```
	 */
	protected function set_dependency() {}

	/**
	 * Sets onboarding prefix.
	 *
	 * @since 1.0
	 */
	abstract protected function set_prefix();

	/**
	 * Sets onboarding page slug.
	 *
	 * @since 1.0
	 */
	abstract protected function set_page();

	/**
	 * Sets onboarding root URL relative to current plugin's directory.
	 *
	 * @since 1.0
	 */
	abstract protected function set_url();

	/**
	 * Sets onboarding root path relative to current plugin's directory.
	 *
	 * @since 1.0
	 */
	abstract protected function set_path();

	/**
	 * Sets onboarding HTML head title.
	 *
	 * Override this to set own head title.
	 *
	 * @since 1.0
	 */
	protected function set_title() {
		$this->title = __( 'TheWebSolver &rsaquo; Onboarding', 'tws-onboarding' );
	}

	/**
	 * Sets user capability to run onboarding wizard.
	 *
	 * @since 1.0
	 */
	abstract protected function set_capability();

	/**
	 * Sets onboarding logo.
	 *
	 * Set logo args in an array as below:
	 * * `string` `href` The logo destination URL.
	 * * `string` `alt` The logo alt text.
	 * * `string` `width` The logo width.
	 * * `string` `height` The logo height.
	 * * `string` `src` The logo image source.
	 *
	 * @since 1.0
	 */
	abstract protected function set_logo();

	/**
	 * Initialize onboarding wizard.
	 *
	 * It will call all abstract methods and set respective properties.
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->set_prefix();
		$this->set_url();
		$this->set_path();
		$this->set_title();
		$this->set_page();
		$this->set_capability();
		$this->set_logo();

		$this->set_dependency();
		if ( 0 < strlen( $this->slug ) ) {
			$this->prepare_dependency();
		}

		// Prepare admin user to have the given capability.
		if ( false === $this->is_active ) {
			add_filter( 'user_has_cap', array( $this, 'add_user_capability' ) );
		}

		// Bail if user has no permission.
		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		// Run admin hooks after user capability has been set.
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'start' ), 99 );

		// Run dependency plugin installation via Ajax.
		add_action( 'wp_ajax_silent_plugin_install', array( $this, 'install_dependency' ) );
	}

	/**
	 * Prepares dependency plugin data.
	 *
	 * @since 1.0
	 */
	protected function prepare_dependency() {
		// Get dependency plugin status.
		$filename           = '' !== $this->filename ? $this->filename : $this->slug;
		$basename           = $this->slug . '/' . $filename . '.php';
		$this->is_installed = TheWebSolver::maybe_plugin_is_installed( $basename );
		$this->is_active    = TheWebSolver::maybe_plugin_is_active( $basename );

		// Set dependency plugin's name and status on clean install.
		if ( false === get_option( $this->prefix . '_onboarding_dependency_status' ) ) {
			if ( $this->is_installed ) {
				// Get plugin info from plugin data.
				$info = TheWebSolver::get_plugin_data( $this->slug . '/' . $this->filename . '.php' );

				if ( is_array( $info ) && ! empty( $info ) ) {
					$name = isset( $info['Name'] ) ? $info['Name'] : '';
					update_option( $this->prefix . '_onboarding_dependency_name', $name );
				}
				update_option( $this->prefix . '_onboarding_dependency_status', 'installed' );
			} else {
				$name = TheWebSolver::get_plugin_info_from_wp_api( $this->slug, 'name' );
				update_option( $this->prefix . '_onboarding_dependency_name', $name );

				$info = TheWebSolver::get_plugin_info_from_wp_api( $this->slug, 'version', $this->version );

				/**
				 * WPHOOK: Filter -> Info about the dependency plugin.
				 *
				 * @param mixed  $info   The dependency plugin info.
				 * @param string $prefix The onboarding prefix.
				 * @param string $slug   The dependency plugin slug.
				 *
				 * @since 1.0
				 */
				apply_filters( 'hzfex_onboarding_dependency_plugin_info', $info, $this->prefix, $this->slug );

				// If latest version of dependency plugin not compatible with WP or PHP, $info => WP_Error.
				$status = is_wp_error( $info ) ? $info->get_error_message() : 'pending';

				update_option( $this->prefix . '_onboarding_dependency_status', $status );
			}
		}

		$this->name = get_option( $this->prefix . '_onboarding_dependency_name', '' );
	}

	/**
	 * Gives admin the capability needed.
	 *
	 * @param array $capabilities The current user capabilities.
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public function add_user_capability( $capabilities ) {
		// Bail early if given cap is of admin.
		if ( 'manage_options' === $this->capability ) {
			return $capabilities;
		}

		if (
			! empty( $capabilities['manage_options'] ) &&
			( ! isset( $capabilities[ $this->capability ] ) || true !== $capabilities[ $this->capability ] )
			) {
			$capabilities[ $this->capability ] = true;
		}

		return $capabilities;
	}

	/**
	 * Add menu for plugin onboarding.
	 *
	 * @since 1.0
	 */
	public function add_page() {
		$this->hook_suffix = add_dashboard_page( '', '', $this->capability, $this->page, '' );
	}

	/**
	 * Starts onboarding.
	 *
	 * @since 1.0
	 */
	public function start() {
		// Bail early if not on setup page.
		if ( empty( $_GET['page'] ) || $this->page !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$this->get_all_steps();
		$this->set_recommended_plugins();

		// Remove recommended step if no data or user has no permission.
		if ( 0 === count( $this->recommended ) || ! current_user_can( 'install_plugins' ) ) {
			unset( $this->steps['recommended'] );
		}

		// Get current step from all steps added to query arg.
		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) ); // phpcs:ignore WordPress.Security.NonceVerification

		$this->register_scripts();

		// Save data of current step with callback function set with callback function on "save" key of that step.
		if ( isset( $_POST['save_step'] ) && 'save_step' === $_POST['save_step'] && isset( $this->steps[ $this->step ]['save'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			call_user_func_array( $this->steps[ $this->step ]['save'], array( $this ) );
		}

		ob_start();
		$this->set_step_header();
		$this->set_step_progress();
		$this->set_step_content();
		$this->set_step_footer();
		exit;
	}

	/**
	 * Gets all steps of onboarding wizard.
	 *
	 * @since 1.0
	 */
	protected function get_all_steps() {
		// Let's not change the intro page.
		$step['introduction'] = array(
			'name'  => __( 'Introduction', 'tws-onboarding' ),
			'image' => array( $this, 'introduction_image' ),
			'view'  => array( $this, 'introduction' ),
		);

		$all_steps = array_merge( $step, $this->set_steps() );

		$all_steps['recommended'] = array(
			'name' => __( 'Recommended', 'tws-onboarding' ),
			'view' => array( $this, 'recommended_view' ),
			'desc' => __( 'Downlonad, install & activate recommended plugins.', 'tws-onboarding' ),
			'save' => array( $this, 'recommended_save' ),
		);

		$all_steps['ready'] = array(
			'name' => __( 'Ready!', 'tws-onboarding' ),
			'desc' => __( 'Everything is set. Let\'s start.', 'tws-onboarding' ),
			'view' => array( $this, 'final_step' ),
			'save' => '',
		);

		/**
		 * WPHOOK: Filter -> Plugin onboarding steps.
		 *
		 * Useful to change step name.
		 *
		 * @param array $steps Onboarding steps.
		 * @param string $prefix The onboarding prefix.
		 *
		 * @since 1.0
		 */
		$this->steps = apply_filters( 'hzfex_set_onboarding_steps', $all_steps, $this->prefix );
	}

	/**
	 * Sets all onboarding steps except introduction.
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	abstract protected function set_steps();

	/**
	 * Sets steps header HTML.
	 *
	 * @since 1.0
	 */
	protected function set_step_header() {
		set_current_screen( $this->hook_suffix );
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php echo esc_html( $this->title ); ?></title>
			<?php
			wp_print_scripts( 'onboarding_script' );
			wp_print_styles( 'onboarding_style' );
			do_action( 'admin_print_styles' );
			do_action( 'admin_head' );
			?>
		</head>
		<body class="onboarding admin-onboarding wp-core-ui <?php echo $this->is_installed ? ' tws-onboarding' : 'tws-onboarding-no'; ?>-<?php echo esc_html( $this->slug ); ?>">
			<!-- onboarding_header -->
			<header id="onboarding_header" class="hz_flx row center">
			<?php if ( $this->logo['src'] ) : ?>
				<h1>
					<a
						id="hz_onboarding_logo"
						href="<?php echo esc_url( $this->logo['href'] ); ?>"
					>
						<img
							src="<?php echo esc_url( $this->logo['src'] ); ?>"
							alt="<?php echo esc_attr( $this->logo['alt'] ); ?>"
							width="<?php echo esc_attr( $this->logo['width'] ); ?>"
							height="<?php echo esc_attr( $this->logo['height'] ); ?>"
						/>
					</a>
				</h1>
			<?php endif; ?>
			<?php if ( 'introduction' === $this->get_step() ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'onboarding', 'introduction', admin_url() ) ); ?>" class="button button-large hz_dyn_btn onboarding_dashboard_btn">← <?php esc_html_e( 'Dashboard', 'tws-onboarding' ); ?></a>
			<?php endif; ?>
			</header>
			<!-- #onboarding_header -->
			<!-- main -->
			<main id="main">
		<?php
	}

	/**
	 * Sets steps progress HTML.
	 *
	 * @since 1.0
	 */
	protected function set_step_progress() {
		?>
		<!-- onboarding_steps -->
		<aside class="onboarding_steps">
			<div class="onboarding_steps_wrapper">
				<ol class="steps_wrapper hz_flx column">
					<?php
					$current_step = 0;
					foreach ( $this->steps as $key => $step ) :
						$current_step++;
						?>
						<li
						id="hz_oBstep__<?php echo esc_attr( $current_step ); ?>"
						class="step
							<?php
							if ( $key === $this->step ) :
								echo 'active';
							elseif ( array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $key, array_keys( $this->steps ), true ) ) :
								echo 'done';
							else :
								echo 'next';
							endif;
							?>
							onboarding-step <?php echo esc_attr( $key ); ?>"
							>
						<?php
						echo '<span class="onboarding_step_counter">' . esc_html( $current_step ) . '</span>';
						echo '<span class="onboarding_step_name only_desktop">' . esc_html( $step['name'] ) . '</span>';

						if ( isset( $step['desc'] ) ) {
							echo '<span class="onboarding_step_desc only_desktop">' . wp_kses_post( $step['desc'] ) . '</span>';
						}
						?>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</aside>
		<!-- .onboarding_steps -->
		<?php
	}

	/**
	 * Sets the current step content HTML.
	 *
	 * @since 1.0
	 */
	protected function set_step_content() {
		// Redirect to admin with added query arg if no views in the step.
		if ( empty( $this->steps[ $this->step ]['view'] ) ) {
			wp_safe_redirect( esc_url_raw( add_query_arg( 'step', 'introduction' ) ) );
			exit;
		}
		?>
		<!-- onboarding_content -->
		<section class="onboarding_content content_step__<?php echo esc_attr( $this->step ); ?>">
			<div class="onboarding_step_image">
			<?php
			if ( isset( $this->steps[ $this->step ]['image'] ) ) :
				call_user_func( $this->steps[ $this->step ]['image'] );
			endif;
			?>
			</div>
			<?php call_user_func( $this->steps[ $this->step ]['view'] ); ?>
		</section>
		<!-- .onboarding_content -->
		<?php
	}

	/**
	 * Sets steps footer HTML.
	 *
	 * @since 1.0
	 */
	protected function set_step_footer() {
		$steps = array_keys( $this->steps );
		$last  = array_pop( $steps );
		if ( $last === $this->step ) :
			?>
			<!-- footer -->
			<footer id="footer">
				<a
				class="onboarding-return onboarding_dashboard_btn button"
				href="<?php echo esc_url( admin_url() ); ?>">
					← <?php esc_html_e( 'Return to Dashboard', 'tws-onboarding' ); ?>
				</a>
			</footer>
			<!-- #footer -->
		<?php endif; ?>
		</main>
		<!-- #main -->
		</body>
		</html>
		<?php
	}

	/**
	 * Sets the Welcome/Introduction page contents.
	 *
	 * @since 1.0
	 */
	protected function introduction() {
		$title       = __( 'Welcome to the Onboarding Wizard', 'tws-onboarding' );
		$description = __( 'Thank you for selecting this plugin for your next WordPress Project. Make sure that your dependency plugin is installed and active. If it is not, we will let you installed right from this page.', 'tws-onboarding' );
		$start       = __( 'Let\'s Start', 'tws-onboarding' );
		$skip        = __( 'Skip & Continue', 'tws-onboarding' );
		$button_text = $this->is_installed ? $start : $skip;
		$status      = get_option( $this->prefix . '_onboarding_dependency_status' );
		$dy_title    = sprintf(
			'%1$s <b>%2$s</b>. %3$s',
			__( 'For this plugin to work, it needs', 'tws-onboarding' ),
			$this->name,
			__( 'You can easily install it from here.', 'tws-onboarding' )
		);

		/**
		 * WPHOOK: Filter -> Default intro information.
		 *
		 * @param array $args    The content title and desccription.
		 * @param string $prefix The onboarding prefix.
		 *
		 * @since 1.0
		 */
		$intro_args = apply_filters(
			'hzfex_onboarding_intro_default_content',
			array(
				'title' => $title,
				'desc'  => $description,
			),
			$this->prefix
		);

		$dependency_args = array(
			'slug'        => $this->slug,
			'name'        => $this->name,
			'status'      => $status, // Can be "installed", "pending" or "WP_Error message".
			'next_step'   => $this->get_next_step_link(),
			'button_text' => $button_text,
		);

		?>
		<!-- Introduction -->
		<h2><?php echo wp_kses_post( $intro_args['title'] ); ?></h2>
		<p><?php echo wp_kses_post( $intro_args['desc'] ); ?></p>
		<!-- #Introduction -->

		<?php
		if ( ! $this->is_installed ) :
			if ( 0 < strlen( $this->slug ) ) :
				?>
				<!-- Dependency Plugin Installation -->
				<?php if ( 'pending' === $status ) : ?>
					<p class="onboarding-dy-title"><?php echo wp_kses_post( $dy_title ); ?></p>
				<?php endif; ?>
				<?php TheWebSolver::get_template( 'dependency.php', $dependency_args, '', trailingslashit( $this->path ) . 'templates/' ); ?>
				<!-- #Dependency Plugin Installation -->
				<?php
			endif;
		else :
			?>
			<!-- Action Buttons -->
			<p id="hz_dyn_btnWrapper" class="hz_dyn_btnWrapper hz_step_actions step onboarding-actions <?php echo esc_attr( $status ); ?>">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-next hz_dyn_btn main_btn hz_btn__prim"><?php echo esc_html( $button_text ); ?> →</a>
			</p>
			<!-- #Action Buttons -->
			<?php
		endif;
	}

	/**
	 * Handles Dependency plugin installation via Ajax.
	 *
	 * @since 1.0
	 * @static
	 */
	public function install_dependency() {
		$msg = __( 'You messed up the code! Please contact the developer :)', 'tws-onboarding' );

		// Bail if ajax errors.
		if ( false === check_ajax_referer( 'silent_installation', '_silent_nonce' ) ) {
			exit( wp_kses_post( $msg ) );
		}

		// Bail early if no data provided.
		if ( ! isset( $_POST['action'] ) ) {
			wp_send_json_error( new \WP_Error( 'invalid_action', $msg ), 404 );
			exit( wp_kses_post( $msg ) );
		}

		$tws_file = trailingslashit( $this->path ) . 'thewebsolver.php';
		if ( ! class_exists( '\TheWebSolver' ) && file_exists( $tws_file ) ) {
			include_once $tws_file;
		}

		// Start installation. Suppress feedback.
		ob_start();

		// NOTE: Sometime installation may throw "An unexpected error occurred" WordPress warning. Shows in debug.log file.
		// Also, plugin activation triggers Ajax in an infinite loop without activation. So, $activate => false.
		$response = TheWebSolver::maybe_install_plugin( $this->slug, $this->filename, $this->version, false );

		// Discard feedback.
		ob_end_clean();

		// Update option to reflect installed status if we get dependency plugin name as response.
		if ( is_string( $response ) ) {
			update_option( $this->prefix . '_onboarding_dependency_status', 'installed' );
		}

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response );
		} else {
			wp_send_json_success( $response );
		}

		// Terminate and stop further execution.
		die();
	}

	/**
	 * Sets the recommended plugins.
	 *
	 * The plugins data in an array.
	 * * `string` `slug`  - The plugin slug (dirname).
	 * * `string` `file`  - The plugin's main file name (excluding `.php`)
	 * * `string` `title` - The plugin title/name.
	 * * `string` `desc`  - The plugin description.
	 * * `string` `logo`  - The plugin logo URL.
	 * * `string` `alt`   - The plugin logo alt text.
	 *
	 * @since 1.0
	 * @example usage
	 * ```
	 * namespace My_Plugin\My_Feature;
	 * use TheWebSolver\Core\Admin\Onboarding\Wizard;
	 *
	 * // Lets assume our child-class is `Onboarding_Wizard` in above namespace.
	 * class Onboarding_Wizard extends Wizard {
	 *  protected function set_recommended_plugins() {
	 *   $this->recommended = array(
	 *    array(
	 *     'slug'  => 'show-hooks',
	 *     'file'  => 'show-hooks',
	 *     'title' => __( 'Show Hooks', 'tws-onboarding' ),
	 *     'desc'  => __( 'A sequential and visual representation of WordPess action and filter hooks.', 'tws-onboarding' ),
	 *     'logo'  => 'https://ps.w.org/show-hooks/assets/icon-256x256.png?rev=2327503',
	 *     'alt'   => __( 'Show Hooks Logo', 'tws-onboarding' ),
	 *    ),
	 *   // Another recommended plugin array args here.
	 *   );
	 *  }
	 * }
	 * ```
	 */
	protected function set_recommended_plugins() {}

	/**
	 * Gets the recommended plugin step's view.
	 *
	 * @since 1.0
	 */
	protected function recommended_view() {
		$title = __( 'Recommended Plugins', 'tws-onboarding' );
		$desc  = __( 'Get the recommended plugins', 'tws-onboarding' );

		/**
		 * WPHOOK: Filter -> default recommended plugin contents.
		 *
		 * @param array  $content `title` and `desc` content.
		 * @param string $prefix   The onboarding prefix.
		 *
		 * @var array
		 *
		 * @since 1.0
		 */
		$recommended = apply_filters(
			'hzfex_onboarding_recommended_default_content',
			array(
				'title' => $title,
				'desc'  => $desc,
			),
			$this->prefix
		);

		// Get the recommended plugins status.
		$text = __( 'Continue', 'tws-onboarding' );
		?>
		<form method="POST">
			<h2><?php echo wp_kses_post( $recommended['title'] ); ?></h2>
			<div><?php echo wp_kses_post( $recommended['desc'] ); ?></div>
			<fieldset id="onboarding-recommended-plugins">

				<?php
				// Get all recommended plugins active status.
				$plugins_status = get_option( $this->prefix . '_get_onboarding_recommended_plugins_status', array() );

				// Get all recommended plugins checked status.
				$plugins_checked = get_option( $this->prefix . '_get_onboarding_recommended_plugins_checked_status', array() );

				foreach ( $this->recommended as $plugin ) :
					$slug = $plugin['slug'];
					$file = isset( $plugin['file'] ) ? $plugin['file'] : $slug;
					$file = $file . '.php';
					$base = $slug . '/' . $file;

					// Get current installed status (maybe deleted outside the scope of onboarding).
					$exists = TheWebSolver::maybe_plugin_is_installed( $base );

					// Get current activated status (maybe activated/deactivated outside the scope of onboarding).
					$is_active = $this->get_active_status( $base );

					// Previous state of all recommended plugins.
					$plugins_status[ $slug ]  = isset( $plugins_status[ $slug ] ) ? $plugins_status[ $slug ] : 'false';
					$plugins_checked[ $slug ] = isset( $plugins_checked[ $slug ] ) ? $plugins_checked[ $slug ] : 'yes';

					// Set current plugin's current active status if any difference in it's status.
					if ( $plugins_status[ $slug ] !== $is_active ) {
						$plugins_status[ $slug ] = $is_active;
					}

					// Recommended plugin deleted/not-installed, force set "checked" => "yes".
					if ( ! $exists ) {
						$plugins_checked[ $slug ] = 'yes';
					}

					// Set actual active status of current plugin.
					$plugin['status'] = $plugins_status[ $slug ];

					// Set actual checked status of current plugin.
					$plugin['checked'] = $plugins_checked[ $slug ];
					?>

					<div class="hz_control_field">
						<?php $this->display_recommended_plugin( $plugin ); ?>
					</div>
					<?php
				endforeach;

				update_option( $this->prefix . '_get_onboarding_recommended_plugins_status', $plugins_status );
				update_option( $this->prefix . '_get_onboarding_recommended_plugins_checked_status', $plugins_checked );

				// Set the button text accordingly.
				if ( in_array( 'false', $plugins_status, true ) ) {
					$text = __( 'Save & Continue', 'tws-onboarding' );
				}
				?>

			</fieldset>
			<?php if ( in_array( 'false', $plugins_status, true ) ) : ?>
				<!-- onboarding-recommended-info contents will be added from "onboarding.js" -->
				<div class="onboarding-recommended-info hz_flx column center">
					<p class="label"><span class="count"></span><span class="suffix"></span></p>
					<p id="onboarding-recommended-names"></p>
				</div>
				<!-- .onboarding-recommended-info -->
				<?php
			endif;
				$this->get_step_buttons( true, true, true, $text );
			?>

		</form>
		<?php
	}

	/**
	 * Installs and activates recommended plugins.
	 *
	 * @since 1.0
	 */
	protected function recommended_save() {
		$this->validate_save();

		// Get all recommended plugins checked status.
		$plugins_checked = get_option( $this->prefix . '_get_onboarding_recommended_plugins_checked_status', array() );

		foreach ( $this->recommended as $plugin ) {
			$slug = $plugin['slug'];
			$file = isset( $plugin['file'] ) ? $plugin['file'] : $slug;

			if ( ! isset( $_POST[ 'onboarding-' . $slug ] ) || 'yes' !== $_POST[ 'onboarding-' . $slug ] ) { // phpcs:ignore WordPress.Security.NonceVerification
				// Set checkbox as not checked for current plugin.
				$plugins_checked[ $slug ] = 'no';
				continue;
			}

			// Set checkbox as checked for current plugin.
			$plugins_checked[ $slug ] = 'yes';

			$this->install_plugin(
				$slug,
				array(
					'name' => $plugin['title'],
					'slug' => $slug,
					'file' => $file . '.php',
				),
				true
			);
		}

		// Finally, update the recommended plugins checked status from checkbox (toggle btn) checked state.
		update_option( $this->prefix . '_get_onboarding_recommended_plugins_checked_status', $plugins_checked );

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Recommended plugins display.
	 *
	 * @param array $data The plugin data in an array.
	 * * `string` `slug`    - The plugin slug.
	 * * `string` `title`   - The plugin title/name.
	 * * `string` `desc`    - The plugin description.
	 * * `string` `logo`    - The plugin logo URL.
	 * * `string` `alt`     - The plugin logo alt text.
	 * * `string` `status`  - The plugin active status.
	 * * `string` `checked` - The plugin checked state.
	 *
	 * @since 1.0
	 */
	protected function display_recommended_plugin( $data ) {
		$slug        = $data['slug'];
		$title       = $data['title'];
		$description = $data['desc'];
		$logo        = $data['logo'];
		$logo_alt    = $data['alt'];
		$status      = $data['status'];
		$checked     = $data['checked'];

		// Set args for data attribute.
		$args = array(
			'slug' => $slug,
			'name' => $title,
		);
		?>
		<div class="recommended-plugin hz_switcher_control <?php echo esc_attr( 'true' === $status ? 'disabled' : 'enabled' ); ?> hz_onboarding_image_icon">
			<label for="<?php echo esc_attr( 'onboarding_recommended_' . $slug ); ?>">
				<input
				id="<?php echo esc_attr( 'onboarding_recommended_' . $slug ); ?>"
				type="checkbox"
				name="<?php echo esc_attr( 'onboarding-' . $slug ); ?>"
				value="yes"
				data-plugin="<?php echo esc_attr( wp_json_encode( $args ) ); ?>"
				data-active="<?php echo esc_attr( $status ); ?>"
				data-control="switch"
				<?php
				if ( 'true' === $status ) :
					echo 'disabled="disabled"';
				else :
					// It will always be "no" if saved more than once after activation.
					echo 'yes' === $checked ? 'checked="checked"' : '';
				endif;
				?>
				/>
				<span class="hz_switcher"></span>
				<figure><img src="<?php echo esc_url( $logo ); ?>" class="<?php echo esc_attr( 'recommended-plugin-icon-' . $slug ); ?> recommended-plugin-icon" alt="<?php echo esc_attr( $logo_alt ); ?>" /></figure>
				<div class="recommended-plugin-desc">
					<p><?php echo esc_html( $title ); ?></p>
					<p class="desc"><?php echo wp_kses_post( $description ); ?></p>
				</div>
			</label>
			<?php if ( 'true' === $status ) : ?>
				<div class="hz_recommended_active_notice hz_flx row center"><span><b><?php echo wp_kses_post( $title ); ?></b> <?php esc_html_e( 'is already active', 'tws-onboarding' ); ?></span></div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Resets (deletes) options added during onboarding.
	 * ------------------------------------------------------------------------------
	 * It will not delete options that are saved on child-class onboarding steps.\
	 * It will only delete options saved for onboarding wizard purpose.
	 * ------------------------------------------------------------------------------
	 *
	 * By default, it is set to an empty array. i.e. onboarding options will not be deleted by default.\
	 * If `$this->reset` array values are passed as an exmaple below, then following options will be deleted.
	 * * ***$this->prefix . '_onboarding_dependency_status'***
	 * * ***$this->prefix . '_onboarding_dependency_name'***
	 * * ***$this->prefix . '_get_onboarding_recommended_plugins_status'***
	 * * ***$this->prefix . '_get_onboarding_recommended_plugins_checked_status'***.
	 *
	 * @since 1.0
	 * @example usage
	 * ```
	 * namespace My_Plugin\My_Feature;
	 * use TheWebSolver\Core\Admin\Onboarding\Wizard;
	 *
	 * // Lets assume our child-class is `Onboarding_Wizard` in above namespace.
	 * class Onboarding_Wizard extends Wizard {
	 *  protected function reset() {
	 *   // Lets keep some options and delete some options. Just pass true/false for following.
	 *   // true will delete option, false will not.
	 *   $this->reset = array(
	 *    'dependency_name'            => true,
	 *    'dependency_status'          => true,
	 *    'recommended_status'         => false,
	 *    'recommended_checked_status' => true,
	 *   );
	 *  }
	 * }
	 * ```
	 */
	protected function reset() {}

	/**
	 * Sets onboarding final step.
	 *
	 * @since 1.0
	 */
	protected function final_step() {
		$this->reset();

		if ( isset( $this->reset['dependency_status'] ) && $this->reset['dependency_status'] ) {
			delete_option( $this->prefix . '_onboarding_dependency_status' );
		}

		if ( isset( $this->reset['dependency_name'] ) && $this->reset['dependency_name'] ) {
			delete_option( $this->prefix . '_onboarding_dependency_name' );
		}

		if ( isset( $this->reset['recommended_status'] ) && $this->reset['recommended_status'] ) {
			delete_option( $this->prefix . '_get_onboarding_recommended_plugins_status' );
		}

		if ( isset( $this->reset['recommended_checked_status'] ) && $this->reset['recommended_checked_status'] ) {
			delete_option( $this->prefix . '_get_onboarding_recommended_plugins_checked_status' );
		}

		/**
		 * Update onboarding steps status option set during plugin activation to `complete`.
		 *
		 * @see {@method `Config::enable_onboarding()`}
		 */
		update_option( $this->prefix . '_onboarding_steps_status', 'complete' );

		/**
		 * WPHOOK: Action -> Fires before final step contents.
		 *
		 * This hook can be used for additional tasks at final step
		 * such as activation of dependency plugin, updating/deleting options.
		 *
		 * @param string $prefix The onboarding prefix.
		 *
		 * @since 1.0
		 */
		do_action( 'hzfex_onboarding_before_final_step_contents', $this->prefix );

		$title = __( 'Onboarding Wizard Completed Successfully!', 'tws-onboarding' );
		$desc  = __( 'Onboarding wizard is complete. Your plugin is now ready!', 'tws-onboarding' );

		/**
		 * WPHOOK: Filter -> Onboarding ready step contents.
		 *
		 * @param array  $content The onboarding final step contents.
		 * @param string $prefix The onboarding prefix.
		 *
		 * @var array $content Onboarding ready step content.
		 *
		 * @since 1.0
		 */
		$content = apply_filters(
			'hzfex_onboarding_wizard_ready',
			array(
				'title' => $title,
				'desc'  => $desc,
			),
			$this->prefix
		);
		?>

		<div class="onboarding_complete">
			<h1><?php echo esc_html( $content['title'] ); ?></h1>
			<p class="onboarding_complete_content"><?php echo wp_kses_post( $content['desc'] ); ?></p>
		</div>

		<?php
		/**
		 * WPHOOK: Action -> Fires at final step action button wrapper.
		 *
		 * @param string $prefix The onboarding prefix.
		 *
		 * @since 1.0
		 */
		do_action( 'hzfex_onboarding_after_final_step_contents', $this->prefix );
	}

	/**
	 * Queues the background silent installation of a recommended plugin.
	 *
	 * @param string $plugin_id   Plugin id used for background install.
	 * @param array  $plugin_info Plugin info array containing.
	 * * `string` `name` - plugin name/title,
	 * * `string` `slug` - plugin's `slug` on wordpress.org repsitory, and
	 * * `string` `file` - plugin's main file-name if different from `[slug].php`, if different from plugin's slug.
	 * @param bool   $activate Whether to activate plugin after installation or not.
	 *
	 * @since 1.0
	 */
	protected function install_plugin( $plugin_id, $plugin_info, $activate = false ) {
		// Make sure we don't trigger multiple simultaneous installs.
		if ( get_option( $this->prefix . '_silent_installing_' . $plugin_id ) ) {
			return;
		}

		// Hook silent installation at WordPress shutdown.
		if ( empty( $this->deferred_actions ) ) {
				add_action( 'shutdown', array( $this, 'run_deferred_actions' ) );
		}

		array_push(
			$this->deferred_actions,
			array(
				'func' => array( '\TheWebSolver', 'silent_plugin_installer' ),
				'args' => array( $plugin_id, $plugin_info, $activate ),
			)
		);

		// Set the background installation flag for this plugin.
		update_option( $this->prefix . '_silent_installing_' . $plugin_id, true );
	}

	/**
	 * Defers action execution.
	 *
	 * It is called after the HTTP request is finished,
	 * so it's executed without the client having to wait for it.
	 * In another words, it runs at WordPress `shutdown` action hook.
	 *
	 * @since 1.0
	 */
	public function run_deferred_actions() {
		$this->close_http_connection();

		// Get all recommended plugins active status.
		$plugins_status = get_option( $this->prefix . '_get_onboarding_recommended_plugins_status', array() );

		// Iterate over deferred actions and run them one by one at shutdown.
		foreach ( $this->deferred_actions as $action ) {
			// Call TheWebSolver::silent_plugin_installer() and it's args.
			$response = call_user_func_array( $action['func'], $action['args'] );

			if (
				isset( $action['func'][1] ) &&
				'silent_plugin_installer' === $action['func'][1] &&
				isset( $action['args'][0] )
			) {
				/**
				 * Clear the background installation flag for current plugin.
				 *
				 * This is to restart installation/activation process of the plugin
				 * if wizard recommended plugin step is run again.
				 *
				 * {@see @method `Wizard::install_plugin()`}
				 */
				delete_option( $this->prefix . '_silent_installing_' . $action['args'][0] );

				/**
				 * Set activation status of recommended plugin.
				 *
				 * This is to prevent being stuck on active status
				 * even though the plugin may have been
				 * activated/deactivated outside the scope of onboarding.
				 *
				 * {@see @method `Wizard::recommended_view()`}
				 */
				$plugins_status[ $action['args'][0] ] = true === $response ? 'true' : 'false';
			}
		}

		// Finally, update the recommended plugins active status from callback response.
		update_option( $this->prefix . '_get_onboarding_recommended_plugins_status', $plugins_status );
	}

	/**
	 * Registers necessary styles/scripts.
	 *
	 * @since 1.0
	 */
	protected function register_scripts() {
		// Scripts.
		wp_register_script( 'hzfex_select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.min.js', array( 'jquery' ), '4.0.12', true );

		/**
		 * WPHOOK: Filter -> Onboarding registered scripts.
		 *
		 * Use this filter to add own scripts to onboarding wizard.
		 *
		 * @param array  $handles The already registered script handles.
		 * @param string $prefix  The onboarding prefix.
		 *
		 * @example usage
		 * ```
		 * add_filter( 'hzfex_register_onboarding_scripts', 'dependency_scripts', 10, 2 );
		 * function dependency_scripts( array $handles, string $prefix ): array {
		 *  // Check if prefix set in `Config::PREFIX` matches before proceeding.
		 *  if ( 'my-prefix' !== $prefix ) {
		 *   return $handles;
		 *  }
		 *
		 *  // Register new script. (NO NEED TO ENQUEUE IT).
		 *  wp_register_script( 'my-new-script', 'path/to/my-new-script.js', array(), '1.0', false );
		 *
		 * // Then add the newly registered script handle to the $handles.
		 *  $handles[] = 'my-new-script';
		 *
		 *  // Return all the dependency handles including newly registered above.
		 *  return $handles;
		 * }
		 *```
		 *
		 * @var array
		 *
		 * @since 1.0
		 */
		$script_handles = apply_filters( 'hzfex_register_onboarding_scripts', array( 'jquery', 'hzfex_select2' ), $this->prefix );

		wp_register_script( 'onboarding_script', $this->url . 'Assets/onboarding.js', $script_handles, '1.0', false );

		wp_localize_script(
			'onboarding_script',
			'tws_ob',
			array(
				'ajaxurl'     => esc_url( admin_url( 'admin-ajax.php' ) ),
				'successText' => __( 'installed successfully.', 'tws-onboarding' ),
				'successNext' => __( 'Continue Next Step', 'tws-onboarding' ),
				'successStar' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon_stars" width="70" height="25" viewBox="0 0 20 30" stroke-width="2.5" stroke="#d84315" fill="none" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 17.75l-6.172 3.245 1.179-6.873-4.993-4.867 6.9-1.002L12 2l3.086 6.253 6.9 1.002-4.993 4.867 1.179 6.873z"></path>
				<path transform="translate(280, 470) rotate(-3.000000) translate(-280, -480)" d="M12 17.75l-6.172 3.245 1.179-6.873-4.993-4.867 6.9-1.002L12 2l3.086 6.253 6.9 1.002-4.993 4.867 1.179 6.873z"></path>
				<path transform="translate(280, 499) rotate(3.000000) translate(-280, -480)" d="M12 17.75l-6.172 3.245 1.179-6.873-4.993-4.867 6.9-1.002L12 2l3.086 6.253 6.9 1.002-4.993 4.867 1.179 6.873z"></path>
				</svg>',
				'errorText'   => __( 'installation error.', 'tws-onboarding' ),
				'ajaxdata'    => array(
					'_silent_nonce' => wp_create_nonce( 'silent_installation' ),
					'action'        => 'silent_plugin_install',
				),
				'recommended' => array(
					'single' => __( 'Plugin', 'tws-onbaording' ),
					'plural' => __( 'Plugins', 'tws-onboarding' ),
					'suffix' => __( 'will be installed and/or activated', 'tws-onboarding' ),
					'select' => __( 'Select one or more plugins above to install/activate', 'tws-onboarding' ),
					'check'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="hz_ob_check"><g class="paths"><path fill="currentColor" d="M504.5 144.42L264.75 385.5 192 312.59l240.11-241a25.49 25.49 0 0 1 36.06-.14l.14.14L504.5 108a25.86 25.86 0 0 1 0 36.42z" class="secondary"></path><path fill="currentColor" d="M264.67 385.59l-54.57 54.87a25.5 25.5 0 0 1-36.06.14l-.14-.14L7.5 273.1a25.84 25.84 0 0 1 0-36.41l36.2-36.41a25.49 25.49 0 0 1 36-.17l.16.17z" class="primary"></path></g></svg>',
				),
			),
		);

		// Styles and fonts.
		wp_register_style( 'hzfex_select2_style', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/css/select2.min.css', array(), '4.0.12', 'all' );
		wp_register_style( 'googleFont', 'https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300&display=swap', array(), '1.0' );

		/**
		 * WPHOOK: Filter -> Onboarding registered styles.
		 *
		 * Use this filter to add own styles to onboarding wizard.
		 *
		 * @param array  $handles The already registered style handles.
		 * @param string $prefix  The onboarding prefix.
		 *
		 * @example usage
		 * ```
		 * add_filter( 'hzfex_register_onboarding_styles', 'dependency_styles', 10, 2 );
		 * function dependency_styles( array $handles, string $prefix ): array {
		 *  // Check if prefix set in `Config::PREFIX` matches before proceeding.
		 *  if ( 'my-prefix' !== $prefix ) {
		 *   return $handles;
		 *  }
		 *
		 *  // Register new style. (NO NEED TO ENQUEUE IT).
		 *  wp_register_style( 'my-new-style', 'path/to/my-new-style.css', array(), '1.0' );
		 *
		 * // Then add the newly registered style handle to the $handles.
		 *  $handles[] = 'my-new-style';
		 *
		 *  // Return all the dependency handles including newly registered above.
		 *  return $handles;
		 * }
		 *```
		 *
		 * @var array
		 *
		 * @since 1.0
		 */
		$style_handles = apply_filters( 'hzfex_register_onboarding_styles', array( 'hzfex_select2_style', 'googleFont' ), $this->prefix );
		wp_register_style( 'onboarding_style', $this->url . 'Assets/onboarding.css', $style_handles, '1.0' );

		/**
		 * WPHOOK: Action -> fires after enqueue onboarding styles and scripts.
		 *
		 * @param array $handles The registered scripts and styles for onboarding wizard.
		 * @param string $prefix The onboarding prefix.
		 *
		 * @since 1.0
		 */
		do_action(
			'hzfex_onboarding_register_scripts',
			array(
				'script' => $script_handles,
				'style'  => $style_handles,
			),
			$this->prefix
		);
	}

	/**
	 * Validates nonce before saving.
	 *
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between 0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
	 *
	 * @since 1.0
	 */
	protected function validate_save() {
		return check_admin_referer( 'hzfex-onboarding' );
	}

	/**
	 * Gets onboarding step action buttons.
	 *
	 * @param bool   $prev        The previous step button.
	 * @param bool   $next        The next step button.
	 * @param bool   $skip        The skip step button.
	 * @param string $submit_text The submit button text.
	 *
	 * @since 1.0
	 */
	public function get_step_buttons( $prev = false, $next = true, $skip = true, $submit_text = '' ) {
		$submit = '' === $submit_text ? __( 'Save & Continue' ) : $submit_text;
		?>
		<!-- onboarding-actions -->
		<fieldset class="onboarding-actions step <?php echo esc_attr( $this->get_step() ); ?> hz_flx column center">
			<?php if ( $prev ) : ?>
				<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>" class="button button-large button-prev hz_btn__prev hz_btn__prim hz_btn__nav">← <?php esc_html_e( 'Previous Step', 'tws-onboarding' ); ?></a>
			<?php endif; ?>
			<?php if ( $skip ) : ?>
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next hz_btn__skip hz_btn__nav"><?php esc_html_e( 'Skip this Step', 'tws-onboarding' ); ?></a>
			<?php endif; ?>
			<?php if ( $next ) : ?>
				<input type="submit" class="button-primary button button-large button-next hz_btn__prim" value="<?php echo esc_attr( $submit ); ?> →" />
				<?php
				wp_nonce_field( 'hzfex-onboarding' );

				/**
				 * Without this hidden input field, save function call will not trigger.
				 *
				 * {@see @method Wizard::start()}
				 */
				?>
				<input type="hidden" name="save_step" value="save_step">
			<?php endif; ?>
		</fieldset>
		<!-- .onboarding-actions -->
		<?php
	}

	/**
	 * Gets the current onboarding step.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_step() {
		return $this->step;
	}

	/**
	 * Gets all onboarding steps.
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public function get_steps() {
		return $this->steps;
	}

	/**
	 * Gets the previous step in queue.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_previous_step_link() {
		$steps = array_keys( $this->steps );
		$index = array_search( $this->step, $steps, true );

		return add_query_arg( 'step', $steps[ $index - 1 ] );
	}

	/**
	 * Gets the next step in queue.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function get_next_step_link() {
		$steps = array_keys( $this->steps );
		$index = array_search( $this->step, $steps, true );

		return add_query_arg( 'step', $steps[ $index + 1 ] );
	}

	/**
	 * Gets active status and sets bool value as string.
	 *
	 * @param string $basename The plugin basename.
	 *
	 * @return string true if active, false otherwise.
	 *
	 * @since 1.0
	 */
	private function get_active_status( $basename ) {
		return TheWebSolver::maybe_plugin_is_active( $basename ) ? 'true' : 'false';
	}

	/**
	 * Finishes replying to the client, but keeps the process running for further (async) code execution.
	 *
	 * @see https://core.trac.wordpress.org/ticket/41358.
	 */
	protected function close_http_connection() {
		// Only 1 PHP process can access a session object at a time, close this so the next request isn't kept waiting.
		if ( session_id() ) {
			session_write_close();
		}

		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}

		// fastcgi_finish_request is the cleanest way to send the response and keep the script running, but not every server has it.
		if ( is_callable( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		} else {
			// Fallback: send headers and flush buffers.
			if ( ! headers_sent() ) {
					header( 'Connection: close' );
			}
			@ob_end_flush(); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			flush();
		}
	}

	/**
	 * Sets introduction page image.
	 *
	 * @since 1.0
	 */
	protected function introduction_image() {
		$image = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1298.1925 653.5638"><defs><style>.cls-1{fill:none;}.cls-2{isolation:isolate;}.cls-3{fill:#2f2e41;}.cls-4{fill:#20202d;}.cls-5{fill:#ffb7b7;}.cls-6{fill:#cbcbcb;}.cls-7{fill:#ffb8b8;}.cls-8{fill:#030d11;}.cls-9{fill:#0a1214;}.cls-10,.cls-16{fill:#09171e;}.cls-11{fill:#fff;}.cls-12{fill:#f7a4a4;}.cls-13{fill:#f9c2c2;}.cls-14{fill:#030e11;}.cls-15{fill:#061d26;}.cls-16{stroke:#000;stroke-miterlimit:10;}.cls-17{fill:#07141c;}.cls-18{fill:#07235b;}.cls-19{fill:#e6e6e6;}.cls-20{fill:#3f3d56;}.cls-21{clip-path:url(#clip-path);}.cls-22{opacity:0.4;mix-blend-mode:multiply;}</style><clipPath id="clip-path" transform="translate(0 0.0007)"><rect class="cls-1" x="36.9981" width="406.0402" height="204.5394"/></clipPath></defs><title>Introduction</title><g class="cls-2"><g id="Layer_2" data-name="Layer 2"><g id="bfbaf7a0-974e-4549-a5a8-91cbda3f7252"><g id="leaves"><path class="cls-3" d="M768.6013,643.664h-148a1,1,0,0,1,0-2h148a1,1,0,0,1,0,2Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M713.2716,642.6571l-.9015-.338c-.1981-.0746-19.9145-7.6286-29.1506-24.7571s-4.7138-37.7529-4.667-37.9589l.2129-.9392.901.338c.1982.0747,19.9142,7.6286,29.1506,24.7572s4.7138,37.7529,4.667,37.9588Zm-28.5731-25.8923c7.8087,14.4822,23.3893,21.9058,27.3369,23.6005.75-4.231,3.1044-21.3406-4.6974-35.8089-7.801-14.4666-23.3876-21.9016-27.3369-23.6005C679.25,585.1894,676.8972,602.2973,684.6985,616.7648Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M690.278,611.5536c16.5984,9.9862,22.991,29.8214,22.991,29.8214s-20.5187,3.6445-37.1171-6.3417-22.991-29.8213-22.991-29.8213S673.68,601.5675,690.278,611.5536Z" transform="translate(0 0.0007)"/></g><g id="girl"><path class="cls-4" d="M1297.1925,641.3653h-381a1,1,0,0,1,0-2h381a1,1,0,0,1,0,2Z" transform="translate(0 0.0007)"/><path class="cls-5" d="M1110.37,411.3305a9.024,9.024,0,0,1-1.5641-12.6656q.0318-.0406.0639-.081a9.2843,9.2843,0,0,1,1.0264-1.0905l2.6889-118.3693,19.4074,3.4652-8.7659,115.8784a9.0407,9.0407,0,0,1-.02,11.2329A9.203,9.203,0,0,1,1110.37,411.3305Z" transform="translate(0 0.0007)"/><path class="cls-6" d="M1115.7281,257.2786c.2349-.0568.4716-.1074.7118-.1529a13.8421,13.8421,0,0,1,16.1572,11.0522q.12.637.179,1.283l.54,5.8673-2.12,63.3166-20.6523-.1141-4.37-52.9377-.9881-13.88A13.838,13.838,0,0,1,1115.7281,257.2786Z" transform="translate(0 0.0007)"/><polygon class="cls-5" points="1032.675 626.298 1021.231 626.297 1015.788 582.16 1032.677 582.16 1032.675 626.298"/><path class="cls-3" d="M1035.5933,622.5606l-22.5356-.0009h-.0009a14.3622,14.3622,0,0,0-14.3614,14.3612v.4667l36.8972.0013Z" transform="translate(0 0.0007)"/><polygon class="cls-5" points="1209.423 612.905 1199.066 617.77 1175.373 580.134 1190.66 572.955 1209.423 612.905"/><path class="cls-3" d="M1210.4768,608.2816l-20.3979,9.58-.0008,0a14.362,14.362,0,0,0-6.8932,19.1043l.1984.4225,33.3971-15.6855Z" transform="translate(0 0.0007)"/><circle class="cls-7" cx="1112.0427" cy="221.5627" r="26.735" transform="translate(30.0009 560.5541) rotate(-28.6632)"/><path class="cls-8" d="M1104.3167,260.38l24.3143-7.1513,16.3049,41.1914-30.3214,62.6451-.5721,11.156s21.3311,26.1361,23.17,64.3615l8.0095,22.0259,9.44,20.8817,57.023,116.6816-23.4607,14.7983L1084.6357,434.8049,1033.744,612.7455l-21.3433,1.1952L1032.3,388.9663l22.017-44.3949,9.7452-22.017s-11.0531-24.1155,10.3452-35.4156Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M1083.5548,192.2106c6.61-5.4421,17.34-4.9244,24.751-.1107a24.0713,24.0713,0,0,1,28.2627,2.9952c7.3882,7.0073,9.3213,19.1034,5.38,28.5195a31.43,31.43,0,0,0,4.7436,29.0993c-7.166.4181-14.3315.8363-21.5146.957a25.67,25.67,0,0,1-4.5874-3.7c-2.857,3.0756-5.7344,4.6561-6.3077,4.5605a94.9789,94.9789,0,0,0-23.1372-.0791c3.6465-11.5948,7.293-23.19,11.7666-33.4553-7.5337,4.0689-17.9638,1.4967-22.7422-5.6084S1076.9444,197.6528,1083.5548,192.2106Z" transform="translate(0 0.0007)"/><path class="cls-5" d="M1054.0521,412.0446a9.0241,9.0241,0,0,1,1.4579-12.6783c.027-.0213.054-.0426.0812-.0636a9.2865,9.2865,0,0,1,1.254-.8186l30.4475-114.418,18.0484,7.9316-35.7683,110.568a9.0407,9.0407,0,0,1-2.6611,10.9131A9.2027,9.2027,0,0,1,1054.0521,412.0446Z" transform="translate(0 0.0007)"/><path class="cls-6" d="M1095.4847,263.5722c.2417,0,.4837.0065.7278.0188a13.8421,13.8421,0,0,1,13.1052,14.5417q-.0336.6471-.1276,1.289l-.8545,5.83-16.9493,61.0428L1071.34,341.327l8.2-52.481,2.3033-13.7229A13.8381,13.8381,0,0,1,1095.4847,263.5722Z" transform="translate(0 0.0007)"/></g><g id="boy"><g id="Слой_1" data-name="Слой 1"><path class="cls-3" d="M477.2587,652.8682l.1383-.0184a3.1659,3.1659,0,0,0,2.6152-3.5734l-25.1789-211.193-14.11.4383,33.8387,212.2235A2.4511,2.4511,0,0,0,477.2587,652.8682Z" transform="translate(0 0.0007)"/><path class="cls-4" d="M175.2605,653.38l.218-.0043a3.0608,3.0608,0,0,0,3.0006-3.12q-.0024-.1144-.0131-.2287L166.5887,524.4105l-10.364.98,17.0546,126.2914A1.9613,1.9613,0,0,0,175.2605,653.38Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M60.8922,653.38l-.218-.0043a3.0613,3.0613,0,0,1-3.001-3.12q.0022-.1141.013-.228L69.5639,524.4105l10.364.98L62.8728,651.6816A1.9606,1.9606,0,0,1,60.8922,653.38Z" transform="translate(0 0.0007)"/><path class="cls-4" d="M259.0542,653.38l.218-.0043a3.0609,3.0609,0,0,0,3.0006-3.12q-.0024-.1146-.0131-.2286L250.3824,524.4105l-10.364.98L257.073,651.6816A1.9611,1.9611,0,0,0,259.0542,653.38Z" transform="translate(0 0.0007)"/><polygon class="cls-4" points="64.429 512.186 15.861 337.833 123.143 337.833 169.115 506.139 64.429 512.186"/><path class="cls-3" d="M231.0039,526.2473H70.4767A6.0473,6.0473,0,0,1,64.43,520.2v-8.0145a6.0473,6.0473,0,0,1,6.0473-6.0473H231.0039a6.0473,6.0473,0,0,1,6.0473,6.0473h0V520.2a6.0473,6.0473,0,0,1-6.0472,6.0473Z" transform="translate(0 0.0007)"/><path class="cls-4" d="M281.31,526.2473H169.1148a5.0918,5.0918,0,0,1-5.0918-5.0918V511.23a5.0914,5.0914,0,0,1,5.0915-5.0915H281.31a5.0918,5.0918,0,0,1,5.0918,5.0918v9.9255A5.0918,5.0918,0,0,1,281.31,526.2473Z" transform="translate(0 0.0007)"/><path class="cls-7" d="M30.4312,408.8938a58.9548,58.9548,0,0,0,5.559,15.6124c2.125,5.27,13.782-23.6332,13.782-23.6332L51.5137,389.74l2.8289-18.0843.7953-5.0778,3.1672-20.2369-.7817-3.0757-3.807-14.9493.3426-3.4093S42.309,331.41,39.11,343.75c-1.1332,4.3692-2.77,13.6329-4.3231,23.9981-.2422,1.6227-1.609,2.2945-1.8465,3.9582C30.9387,385.8184,29.2157,401.9238,30.4312,408.8938Z" transform="translate(0 0.0007)"/><path class="cls-7" d="M245.339,426.0582a19.1766,19.1766,0,0,0-6.1285-1.9894c-16.1574-1.3907-51.1918-13.3809-54.9-16.1266-6.843-5.0664-35.4394-29.1227-46.712-41.2637-.01-.0078-9.4418-11.0968-9.4418-11.0968-10.6676-11.4836-.9933-31.53-.9933-31.53s18.3191,7.7789,24.946,15.434c4.3262,4.997,37.4376,46,41.57,48.0144,13.4625,6.5633,51.6348,28.9672,55.6177,30.725C249.2969,418.225,254.4684,421.8175,245.339,426.0582Z" transform="translate(0 0.0007)"/><path class="cls-9" d="M346.0589,624.3375s-2.2162,28.7285,2.7137,29.11c.6557.0508,25.0047.0508,25.0047.0508L404.86,653.31s-.2035-7.8148-9.2891-9.4668-27.0677-9.63-32.5716-22.16C357.2091,608.5012,346.0589,624.3375,346.0589,624.3375Z" transform="translate(0 0.0007)"/><path class="cls-8" d="M129.7352,447.9043s2.736,52.0855,7.5054,61.1582c0,0,2.132-.7508,17.69-.8895,18.8269-.1675,108.7355-.5285,119.5183,4.928,3.3722,1.7071,13.0418,42.3,70.7969,112.1473a24.6177,24.6177,0,0,0,14.5295,3.2278c8.3787-.6094,9.0834-2.161,9.0834-2.161s-54.9459-140.4394-67.1129-152.008C283.16,456.6351,188.0062,438.4563,174.0839,438.05S129.7352,447.9043,129.7352,447.9043Z" transform="translate(0 0.0007)"/><path class="cls-9" d="M281.6211,625.5516s-2.2164,27.5324,2.7137,27.8976c.6556.0488,25.0047.0488,25.0047.0488l31.0824-.18s-.2033-7.49-9.2889-9.0727-27.0678-9.2289-32.5717-21.2371C292.7713,610.375,281.6211,625.5516,281.6211,625.5516Z" transform="translate(0 0.0007)"/><path class="cls-8" d="M93.9734,447.9043s.7563,14.4,2.1668,29.375c1.7375,18.4476,13.1766,30.1746,31.6055,32.0992,12.4082,1.2957,27.0723.61,36.7605.556,46.7184-.2609,63.3981-2.29,74.1805,3.1668,3.3722,1.707-12.6668,27.3223,42.0145,112.1473,0,0,4.1222,3.2992,13.2627,4.5988,5.45.7753,11.7429-1.3,11.7429-1.3s-16.9691-133.39-30.7935-148.8179c-9.8844-11.0309-88.2758-23.9684-102.1981-24.375S93.9734,447.9043,93.9734,447.9043Z" transform="translate(0 0.0007)"/><path class="cls-10" d="M30.4308,374.0675s9.579,8.67,25.552,7.1754c.9051,4.63,2.034,9.776,3.3547,15.2235.11.4523.22.9093.3336,1.3664,2.08,8.4777,4.607,17.6363,7.4449,26.672,8.2356,26.2469,19.0946,51.52,29.3868,56.4926,1.0785.521,31.6625.2332,52.5984-7.911a72.5043,72.5043,0,0,0,17.1477-9.9633,133.9907,133.9907,0,0,0,18.304-17.1292,8.2608,8.2608,0,0,0-.6755-11.6629l-.0054-.0047c-7.8793-7.0246-21-19.4375-23.9527-26.7129-2.4906-6.1379-6.1652-19.0489-9.104-29.9809,15.47-4.104,19.0031-20.9317,19.0031-20.9317s-25.3286-27.5767-33.2626-32.65c-10.3832-6.636-24.1214-7.4859-29.5007-8.4091-13.2031-2.2668-28.7512-1.8739-52.996,9.264,0,0-13.7289,7.422-17.3851,21.7406S30.4308,374.0675,30.4308,374.0675Z" transform="translate(0 0.0007)"/><path class="cls-7" d="M93.3508,335.534c13.0129.9855,14.141-9.52,14.141-9.52l-.2942-6.9674-.3425-8.1834s-9.4243-4.9717-16.8535-8.4168c-4.7418-2.196-8.6743-3.7705-8.83-3.0326-1.0864,5.0914-4.2211,15.2379-7.477,19.8023C71.7871,327.117,80.3336,334.5474,93.3508,335.534Z" transform="translate(0 0.0007)"/><path class="cls-7" d="M111.6664,313.0719s-32.68,2.9551-34.2075-17.4248-6.7734-33.724,14.0274-36.4727,25.4422,4.724,27.6066,11.4026S120.1278,311.7777,111.6664,313.0719Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M65.8531,257.7717c-.302-.3432-.9953-.6184-2.5371-.6967a6.2523,6.2523,0,0,1,2.876-.8217c.3144-2.1445,1.4406-5.942,5.1257-6.8777a4.6229,4.6229,0,0,0-2.2289,3.3435c4.256-4.4035,13.3981-7.631,38.7539-5.7244,33.4168,2.5129,22.4864,23.943,18.1711,26.5088s-15.228-4.384-23.2836-3.7146-9.5449,12.3189-12.2726,13.9269-1.6633-1.2213-5.368-2.8793-4.0336,4.472-2.95,8.4527-3.98,9.256-3.98,9.256l-5.156-5.5117c-5.1563-5.5117-10.0832-28.92-7.7668-33.9225C65.4414,258.6691,65.6437,258.2215,65.8531,257.7717Z" transform="translate(0 0.0007)"/><path class="cls-11" d="M115.3672,301.048a5.0917,5.0917,0,0,1-4.77,3.2848,8.2757,8.2757,0,0,1-7.2235-3.5691S110.3844,299.0217,115.3672,301.048Z" transform="translate(0 0.0007)"/><path class="cls-12" d="M99.0527,312.6812s2.5891-4.3015,0-5.17c-13.288-4.456-17.1695-1.94-17.1695-1.94Z" transform="translate(0 0.0007)"/><path class="cls-13" d="M77.475,324.8424s-1.1707-19.6676,5.7379-19.5194,15.44,2.35,15.7672,4.1133c.916,4.9438-7.0457,21.704-10.9965,22.4493S77.475,324.8424,77.475,324.8424Z" transform="translate(0 0.0007)"/><path class="cls-10" d="M59.6711,397.8328c2.08,8.4778,4.607,17.6363,7.4449,26.672,8.2356,26.2469,19.0945,51.52,29.3867,56.4926,1.0785.5211,31.6625.2332,52.5984-7.911a72.5033,72.5033,0,0,0,17.1477-9.9632c-14.0215,4.2183-37.11.2742-58.7594-7.1156-23.1937-7.925-24.332-36.5621-24.332-36.5621-6.7043-15.845,2.0157-70.1073,2.0157-70.1073Z" transform="translate(0 0.0007)"/><path class="cls-13" d="M88.9488,331.3943S70.3715,432.3777,45.8246,432.4363c-9.4567.0223-21.0348-24.55-8.5426-38.5355,17.932-20.0766,40.193-69.0584,40.193-69.0584S80.5887,322.8838,88.9488,331.3943Z" transform="translate(0 0.0007)"/><path class="cls-13" d="M236.4472,416.225s7.7457,1.3141,11.7933,0,28.7926,8.2266,25.8219,8.4551-17.7957-1.9617-21.2516-1.6715-5.3.4527-3.9445,1.6883,10.952,1.5828,7.6007,2.6492-11.5781.7617-15.082,0a42.46,42.46,0,0,1-9.6183-3.643C229.1694,422.3582,236.4472,416.225,236.4472,416.225Z" transform="translate(0 0.0007)"/><path class="cls-14" d="M277.918,432.8848h-62.13a4.9528,4.9528,0,0,1,4.9528-4.9524h57.1773Z" transform="translate(0 0.0007)"/><polygon class="cls-15" points="375.211 365.219 352.171 432.885 255.49 432.885 279.155 365.219 375.211 365.219"/><path class="cls-16" d="M323.423,398.4266a6.2457,6.2457,0,0,1-5.7619,4.6813,3.6048,3.6048,0,0,1-3.6-4.6813,6.2458,6.2458,0,0,1,5.7621-4.6812A3.6045,3.6045,0,0,1,323.423,398.4266Z" transform="translate(0 0.0007)"/><path class="cls-4" d="M57.4619,653.5484l-.1382-.0183a3.166,3.166,0,0,1-2.6153-3.5735l25.179-211.1929,14.11.4383L60.1588,651.4254A2.4511,2.4511,0,0,1,57.4619,653.5484Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M18.6387,653.5484,18.5,653.53a3.1664,3.1664,0,0,1-2.6157-3.5735L41.064,438.7637l14.11.4382L21.3357,651.4254A2.4512,2.4512,0,0,1,18.6387,653.5484Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M473.7088,445.091H6.3273A6.3273,6.3273,0,0,1,0,438.7638H0a6.3273,6.3273,0,0,1,6.3272-6.3274H473.7088a6.3273,6.3273,0,0,1,6.3275,6.3271v0h0a6.3273,6.3273,0,0,1-6.3273,6.3273Z" transform="translate(0 0.0007)"/></g></g><g id="bullet-component"><g id="bullets"><circle class="cls-8" cx="690.7587" cy="106.6309" r="4.3605"/><circle class="cls-8" cx="712.8316" cy="106.6309" r="4.3605"/><circle class="cls-8" cx="734.9047" cy="106.6309" r="4.3605"/><circle class="cls-8" cx="756.9776" cy="106.6309" r="4.3605"/><circle class="cls-8" cx="779.0506" cy="106.6309" r="4.3605"/><circle class="cls-8" cx="690.7587" cy="128.981" r="4.3605"/><circle class="cls-8" cx="712.8316" cy="128.981" r="4.3605"/><circle class="cls-8" cx="734.9047" cy="128.981" r="4.3605"/><circle class="cls-8" cx="756.9776" cy="128.981" r="4.3605"/><circle class="cls-8" cx="779.0506" cy="128.981" r="4.3605"/><circle class="cls-8" cx="823.1966" cy="128.981" r="4.3605"/><circle class="cls-8" cx="690.7587" cy="151.331" r="4.3605"/><circle class="cls-8" cx="712.8316" cy="151.331" r="4.3605"/><circle class="cls-8" cx="734.9047" cy="151.331" r="4.3605"/><circle class="cls-8" cx="756.9776" cy="151.331" r="4.3605"/><circle class="cls-8" cx="779.0506" cy="151.331" r="4.3605"/><circle class="cls-8" cx="690.7587" cy="173.6811" r="4.3605"/><circle class="cls-8" cx="712.8316" cy="173.6811" r="4.3605"/><circle class="cls-8" cx="734.9047" cy="173.6811" r="4.3605"/><circle class="cls-8" cx="756.9776" cy="173.6811" r="4.3605"/><circle class="cls-8" cx="779.0506" cy="173.6811" r="4.3605"/><circle class="cls-8" cx="801.1236" cy="173.6811" r="4.3605"/><circle class="cls-8" cx="823.1966" cy="173.6811" r="4.3605"/><circle class="cls-8" cx="845.2696" cy="173.6811" r="4.3605"/><circle class="cls-8" cx="867.3426" cy="173.6811" r="4.3605"/><circle class="cls-8" cx="889.4156" cy="173.6811" r="4.3605"/><circle class="cls-8" cx="779.0506" cy="196.0311" r="4.3605"/><circle class="cls-8" cx="801.1236" cy="196.0311" r="4.3605"/><circle class="cls-8" cx="823.1966" cy="196.0311" r="4.3605"/><circle class="cls-8" cx="845.2696" cy="196.0311" r="4.3605"/><circle class="cls-8" cx="867.3426" cy="196.0311" r="4.3605"/><circle class="cls-8" cx="889.4156" cy="196.0311" r="4.3605"/><circle class="cls-8" cx="779.0506" cy="218.3812" r="4.3605"/><circle class="cls-8" cx="801.1236" cy="218.3812" r="4.3605"/><circle class="cls-8" cx="823.1966" cy="218.3812" r="4.3605"/><circle class="cls-8" cx="845.2696" cy="218.3812" r="4.3605"/><circle class="cls-8" cx="867.3426" cy="218.3812" r="4.3605"/><circle class="cls-8" cx="889.4156" cy="218.3812" r="4.3605"/><circle class="cls-8" cx="779.0506" cy="240.7312" r="4.3605"/><circle class="cls-8" cx="801.1236" cy="240.7312" r="4.3605"/><circle class="cls-8" cx="823.1966" cy="240.7312" r="4.3605"/><circle class="cls-8" cx="845.2696" cy="240.7312" r="4.3605"/><circle class="cls-8" cx="867.3426" cy="240.7312" r="4.3605"/><circle class="cls-8" cx="889.4156" cy="240.7312" r="4.3605"/><circle class="cls-8" cx="690.7587" cy="263.0813" r="4.3605"/><circle class="cls-8" cx="712.8316" cy="263.0813" r="4.3605"/><circle class="cls-8" cx="734.9047" cy="263.0813" r="4.3605"/><circle class="cls-8" cx="756.9776" cy="263.0813" r="4.3605"/><circle class="cls-8" cx="779.0506" cy="263.0813" r="4.3605"/><circle class="cls-8" cx="801.1236" cy="263.0813" r="4.3605"/><circle class="cls-8" cx="823.1966" cy="263.0813" r="4.3605"/><circle class="cls-8" cx="845.2696" cy="263.0813" r="4.3605"/><circle class="cls-8" cx="867.3426" cy="263.0813" r="4.3605"/><circle class="cls-8" cx="889.4156" cy="263.0813" r="4.3605"/><circle class="cls-8" cx="690.7562" cy="285.4306" r="4.3605"/><circle class="cls-8" cx="712.8316" cy="285.4313" r="4.3605"/><circle class="cls-8" cx="690.7561" cy="307.7805" r="4.3605"/><circle class="cls-8" cx="712.8316" cy="307.7814" r="4.3605"/><circle class="cls-8" cx="756.9776" cy="307.7814" r="4.3605"/></g><g class="spin hz_gear"><path class="cls-17" d="M609.2211,179.0844c-1.9713-.8326-7.069,1.4281-11.8311,4a60.6109,60.6109,0,0,0-9.2259-10.2736c3.0138-4.3493,5.7354-9.0939,5.1683-11.3883-1.1944-4.8326-25.837-16.0247-29.9594-14.3509-1.9837.8054-3.9912,6.0131-5.541,11.2a60.614,60.614,0,0,0-13.7877-.7554c-.9449-5.2048-2.3747-10.48-4.3971-11.7-4.26-2.5714-29.5993,6.94-31.33,11.0389-.8329,1.9712,1.4278,7.0689,3.9982,11.831a60.6035,60.6035,0,0,0-10.2948,9.2487c-4.4593-3.07-9.2874-5.87-11.3378-5.254-4.2612,1.28-16.4477,25.4463-14.3492,29.9594.9961,2.1432,6.09,4.1334,11.1654,5.6339a60.6065,60.6065,0,0,0-.7487,13.7883c-5.3217.9821-10.7107,2.4161-11.7248,4.2993-2.1079,3.9182,6.3641,29.6232,11.0382,31.331,2.2189.8106,7.2239-1.3816,11.8733-3.9079a60.62,60.62,0,0,0,9.2311,10.2695c-3.07,4.4591-5.87,9.2874-5.254,11.3376,1.28,4.2611,25.4463,16.4476,29.9594,14.3491,2.1432-.9961,4.1334-6.09,5.634-11.1653a60.6092,60.6092,0,0,0,13.788.7486c.9823,5.3217,2.4161,10.7109,4.3006,11.7248,3.9179,2.1079,29.6229-6.3641,31.3307-11.0382.8109-2.2189-1.3815-7.2239-3.9077-11.8732a60.6162,60.6162,0,0,0,10.2485-9.2074c4.3494,3.0138,9.094,5.7354,11.3884,5.1682,4.8308-1.1943,16.0227-25.837,14.3491-29.9594-.8053-1.9835-6.0131-3.9907-11.2-5.5407A60.62,60.62,0,0,0,608.56,214.81c5.205-.9449,10.4794-2.3747,11.7-4.3969C622.8306,206.155,613.32,180.8159,609.2211,179.0844ZM508.1287,232.3462a41.9062,41.9062,0,1,1,53.45,25.6h0a41.906,41.906,0,0,1-53.45-25.6Z" transform="translate(0 0.0007)"/></g><g id="message-3"><path class="cls-3" d="M976.9945,153.7154h-172.8a3.2039,3.2039,0,0,1-3.2-3.2V107.651a3.2039,3.2039,0,0,1,3.2-3.2h172.8a3.2039,3.2039,0,0,1,3.2,3.2v42.8644A3.2039,3.2039,0,0,1,976.9945,153.7154Zm-172.8-47.9844a1.9222,1.9222,0,0,0-1.92,1.92v42.8644a1.9221,1.9221,0,0,0,1.92,1.92h172.8a1.9221,1.9221,0,0,0,1.92-1.92V107.651a1.9222,1.9222,0,0,0-1.92-1.92Z" transform="translate(0 0.0007)"/><circle class="cls-18" cx="826.5941" cy="129.0838" r="13.44"/><path class="cls-3" d="M857.6341,120.123a2.24,2.24,0,0,0,0,4.48h59.52a2.24,2.24,0,1,0,0-4.48Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M857.6341,133.563a2.24,2.24,0,0,0,0,4.48h105.6a2.24,2.24,0,0,0,0-4.48Z" transform="translate(0 0.0007)"/></g><g id="message-2"><path class="cls-19" d="M754.9145,240.7554h-172.8a3.2039,3.2039,0,0,1-3.2-3.2V194.691a3.2039,3.2039,0,0,1,3.2-3.2h172.8a3.2039,3.2039,0,0,1,3.2,3.2v42.8644A3.2038,3.2038,0,0,1,754.9145,240.7554Zm-172.8-47.9844a1.9222,1.9222,0,0,0-1.92,1.92v42.8644a1.9221,1.9221,0,0,0,1.92,1.92h172.8a1.9221,1.9221,0,0,0,1.92-1.92V194.691a1.9222,1.9222,0,0,0-1.92-1.92Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M635.5541,207.163a2.24,2.24,0,1,0,0,4.48h62.08a2.24,2.24,0,1,0,0-4.48Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M635.5541,220.603a2.24,2.24,0,1,0,0,4.48h105.6a2.24,2.24,0,1,0,0-4.48Z" transform="translate(0 0.0007)"/></g><g id="message-1"><path class="cls-20" d="M911.0744,334.8354h-172.8a3.2039,3.2039,0,0,1-3.2-3.2V288.771a3.2039,3.2039,0,0,1,3.2-3.2h172.8a3.2037,3.2037,0,0,1,3.2,3.2v42.8644A3.2037,3.2037,0,0,1,911.0744,334.8354Zm-172.8-47.9844a1.9222,1.9222,0,0,0-1.92,1.92v42.8644a1.9221,1.9221,0,0,0,1.92,1.92h172.8a1.922,1.922,0,0,0,1.92-1.92V288.771a1.922,1.922,0,0,0-1.92-1.92Z" transform="translate(0 0.0007)"/><circle class="cls-18" cx="760.6741" cy="310.2038" r="13.44"/><path class="cls-3" d="M791.7141,301.243a2.24,2.24,0,0,0,0,4.48h56.96a2.24,2.24,0,0,0,0-4.48Z" transform="translate(0 0.0007)"/><path class="cls-3" d="M791.7141,314.683a2.24,2.24,0,1,0,0,4.48h105.6a2.24,2.24,0,0,0,0-4.48Z" transform="translate(0 0.0007)"/></g></g><g id="Artwork_40" data-name="Artwork 40"><g class="cls-21"><g class="cls-21"><g class="cls-21"><g class="cls-22"><g class="cls-21"><path class="cls-17" d="M443.0377,25.431a25.43,25.43,0,0,1-25.43,25.4289H62.4278a25.43,25.43,0,1,1,0-50.8606h355.18a25.4307,25.4307,0,0,1,25.43,25.4317" transform="translate(0 0.0007)"/><path class="cls-17" d="M431.6789,101.9a17.8407,17.8407,0,0,1-17.84,17.8414H82.1969a17.84,17.84,0,1,1,0-35.68H413.839a17.8391,17.8391,0,0,1,17.84,17.8384" transform="translate(0 0.0007)"/><path class="cls-17" d="M311.3515,163.8679a14.499,14.499,0,0,1-14.5,14.4987H106.9863a14.4987,14.4987,0,1,1,0-28.9974h189.865a14.4989,14.4989,0,0,1,14.5,14.4987" transform="translate(0 0.0007)"/><path class="cls-17" d="M415.5029,163.8679a14.499,14.499,0,0,1-14.5,14.4987H337.421a14.4987,14.4987,0,1,1,0-28.9974h63.5817a14.4989,14.4989,0,0,1,14.5,14.4987" transform="translate(0 0.0007)"/><path class="cls-17" d="M358.2776,195.9908a8.5494,8.5494,0,0,1-8.55,8.5486H235.4063a8.5494,8.5494,0,1,1,0-17.0987H349.7275a8.5506,8.5506,0,0,1,8.55,8.55" transform="translate(0 0.0007)"/><path class="cls-17" d="M239.2854,84.1341H92.4057a16.6632,16.6632,0,0,0,0-33.3264h146.88a16.6632,16.6632,0,0,0,0,33.3264" transform="translate(0 0.0007)"/><path class="cls-17" d="M406.92,84.1341H307.7748a16.6632,16.6632,0,0,0,0-33.3264H406.92a16.6632,16.6632,0,0,0,0,33.3264" transform="translate(0 0.0007)"/><path class="cls-17" d="M276.8555,149.3687H135.0709a14.8132,14.8132,0,0,0,0-29.6263H276.8555a14.8132,14.8132,0,0,0,0,29.6263" transform="translate(0 0.0007)"/></g></g></g></g></g></g></g></g></g></svg>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="onboarding_image_wrapper">' . $image . '</div>';
	}
}
