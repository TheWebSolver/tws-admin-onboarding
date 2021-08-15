<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver WordPress Admin Onboarding Wizard Abstract Class.
 * Handles installation of dependency plugin at introduction page.
 *
 * @package TheWebSolver\Core\Admin\Onboarding\Abstract
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

if ( ! class_exists( __NAMESPACE__ . '\\Wizard' ) ) {
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
		 * The current onboarding wizard configuration instance.
		 *
		 * @var object
		 *
		 * @since 1.1
		 */
		protected $config;

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
		 *
		 * @since 1.0
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
		 * * *dependency_name*
		 * * *dependency_status*
		 * * *recommended_status*
		 * * *recommended_checked_status*
		 *
		 * @var bool[]
		 *
		 * @since 1.0
		 */
		protected $reset = array();

		/**
		 * Onboarding constructor.
		 *
		 * Everything happens at `init`. Always call it after initializing class!!!
		 *
		 * @see {@method `Wizard::init()`}
		 * @since 1.0
		 */
		public function __construct() {
			// See init().
		}

		/**
		 * Sets current onboarding wizard configuration instance.
		 *
		 * @param object $instance The current config instance in given namespace.
		 *
		 * @since 1.1
		 */
		public function set_config( $instance ) {
			$this->config = $instance;
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
		 * * @property Wizard::$slug     - The plugin's slug on WordPress repository (aka directory name).
		 * * @property Wizard::$filename - The plugin's main file name. Only needed if different than `$slug`.
		 *                                 Don't include the extension `.php`.
		 * * @property Wizard::$version  - The plugin's version to install. Useful if PHP and/or WordPress
		 *                                 not compatible with plugin's latest version. Defaults to `latest`.
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
		 * Sets onboarding steps.
		 *
		 * `introduction`, `recommended` and `ready` steps are created by default.
		 * So, all steps display order will be:
		 * * _Introduction/First step_
		 * * _All other steps added by this method_
		 * * _Recommended step_
		 * * _Ready/Final step_
		 *
		 * Each step should have ***key*** as step id and ***value*** with below args:
		 * * @type `string`   `name` `required` The step name/title.
		 * * @type `string`   `desc` `optional` The step description. Will be shown below name.
		 * * @type `callable` `view` `required` The callable function/method to display step contents.
		 * * @type `callable` `save` `required` The callable function/method to save step contents.
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		abstract protected function set_steps();

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
		 * Initialize onboarding wizard.
		 *
		 * Always call this method after instantiating child class.
		 * It will call all abstract methods and set respective properties.
		 *
		 * @since 1.0
		 */
		public function init() {
			$this->prefix = $this->config->get_prefix();

			$this->set_title();
			$this->set_logo();
			$this->set_dependency();
			$this->set_recommended_plugins();

			if ( 0 < strlen( $this->slug ) ) {
				// Get dependency plugin status.
				$filename           = '' !== $this->filename ? $this->filename : $this->slug;
				$basename           = $this->slug . '/' . $filename . '.php';
				$this->is_installed = TheWebSolver::maybe_plugin_is_installed( $basename );
				$this->is_active    = TheWebSolver::maybe_plugin_is_active( $basename );
			}

			// Exclude dependency plugin from recommended, if included. Not a good idea to include same in both.
			$filtered          = array_filter( $this->recommended, array( $this, 'exclude_dependency_from_recommended' ) );
			$this->recommended = $filtered;

			// Prepare admin user to have the given capability.
			add_filter( 'user_has_cap', array( $this, 'add_user_capability' ) );

			// Bail if user has no permission.
			if ( ! current_user_can( $this->config->get_capability() ) ) {
				return;
			}

			// Run admin hooks after user capability has been set.
			add_action( 'admin_menu', array( $this, 'add_page' ) );
			add_action( 'admin_init', array( $this, 'start' ), 99 );

			// Run dependency plugin installation via Ajax.
			add_action( "wp_ajax_{$this->prefix}_silent_plugin_install", array( $this, 'install_dependency' ) );
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
			// If $this->reset['dependency_status] => true in last step, then status => false.
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
					 * @var mixed
					 * @since 1.0
					 */
					$info = apply_filters( 'hzfex_onboarding_dependency_plugin_info', $info, $this->prefix, $this->slug );

					// If latest version of dependency plugin not compatible with WP or PHP, $info => WP_Error.
					$status = is_wp_error( $info ) ? $info->get_error_message() : 'pending';

					update_option( $this->prefix . '_onboarding_dependency_status', $status );
				}
			}

			$this->name = get_option( $this->prefix . '_onboarding_dependency_name', '' );
		}

		/**
		 * Filters out dependency plugin from recommended plugin.
		 *
		 * This is to prevent showing dependency plugin in list of recommended plugins too.
		 *
		 * @param array $plugin The current recommended plugin.
		 *
		 * @since 1.0
		 */
		public function exclude_dependency_from_recommended( $plugin ) {
			return isset( $plugin['slug'] ) && $plugin['slug'] !== $this->slug;
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
			if ( 'manage_options' === $this->config->get_capability() ) {
				return $capabilities;
			}

			if (
				! empty( $capabilities['manage_options'] ) &&
				( ! isset( $capabilities[ $this->config->get_capability() ] ) || true !== $capabilities[ $this->config->get_capability() ] )
				) {
				$capabilities[ $this->config->get_capability() ] = true;
			}

			return $capabilities;
		}

		/**
		 * Add menu for plugin onboarding.
		 *
		 * @since 1.0
		 */
		public function add_page() {
			$this->hook_suffix = add_dashboard_page( '', '', $this->config->get_capability(), $this->config->get_page(), '' );
		}

		/**
		 * Starts onboarding.
		 *
		 * @since 1.0
		 * @since 1.1 Set step ID for the form handler.
		 */
		public function start() {
			// Bail early if not on setup page.
			if ( ! isset( $_GET['page'] ) || $this->config->get_page() !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			if ( 0 < strlen( $this->slug ) ) {
				$this->prepare_dependency();
			}

			$this->get_all_steps();

			// Remove recommended step if no data or user has no permission.
			if ( 0 === count( $this->recommended ) || ! current_user_can( 'install_plugins' ) ) {
				unset( $this->steps['recommended'] );
			}

			// Get current step from all steps added to query arg.
			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Prepare form for this step.
			$this->config->form->set_step( $this->step );

			$this->register_scripts();

			// Save data of current step set with callback function on "save" key of that step.
			if ( isset( $_POST['save_step'] ) && 'save_step' === $_POST['save_step'] && isset( $this->steps[ $this->step ]['save'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
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
			// Let's set the default intro page.
			$step['introduction'] = array(
				'name'  => __( 'Introduction', 'tws-onboarding' ),
				'image' => array( $this, 'introduction_image' ),
				'view'  => array( $this, 'introduction' ),
			);

			$all_steps = array_merge( $step, $this->set_steps() );

			// And this too.
			$all_steps['recommended'] = array(
				'name' => __( 'Recommended', 'tws-onboarding' ),
				'view' => array( $this, 'recommended_view' ),
				'desc' => __( 'Downlonad, install & activate recommended plugins.', 'tws-onboarding' ),
				'save' => array( $this, 'recommended_save' ),
			);

			// And this final step.
			$all_steps['ready'] = array(
				'name' => __( 'Ready!', 'tws-onboarding' ),
				'desc' => __( 'Everything is set. Let\'s start.', 'tws-onboarding' ),
				'view' => array( $this, 'final_step' ),
				'save' => '',
			);

			/**
			 * WPHOOK: Filter -> Plugin onboarding steps.
			 *
			 * Useful to change step name, desc, set step hero image.
			 *
			 * @param array  $steps  Onboarding steps.
			 * @param string $prefix The onboarding prefix.
			 * @var array
			 * @since 1.0
			 */
			$this->steps = apply_filters( 'hzfex_set_onboarding_steps', $all_steps, $this->prefix );
		}

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
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width" />
				<title><?php echo esc_html( $this->title ); ?></title>
				<?php
				wp_print_scripts( 'onboarding_script' );
				wp_print_styles( 'onboarding_style' );
				do_action( 'admin_print_styles' );
				do_action( 'admin_head' );

				/**
				 * WPHOOK: Filter -> Body classes.
				 *
				 * @param string[] $classes Additional body classes in an array.
				 * @param string   $prefix  The Onboarding prefix.
				 * @var string[]
				 * @since 1.0
				 */
				$classes = apply_filters( 'hzfex_onboarding_body_classes', array(), $this->prefix );
				$classes = ! empty( $classes ) ? implode( ' ', $classes ) : '';
				?>
			</head>
			<body class="onboarding admin-onboarding wp-core-ui <?php echo $this->is_installed ? ' tws-onboarding' : ' no-dependency tws-onboarding-no'; ?>-<?php echo esc_attr( $this->slug ); ?><?php echo esc_attr( $classes ); ?>">
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
				<?php
				$steps = array_keys( $this->steps );
				$first = array_shift( $steps );
				if ( $first === $this->step ) :
					?>
					<a href="<?php echo esc_url_raw( add_query_arg( 'onboarding', 'introduction', admin_url() ) ); ?>" class="button button-large hz_dyn_btn onboarding_dashboard_btn">← <?php esc_html_e( 'Dashboard', 'tws-onboarding' ); ?></a>
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
							<span class="onboarding_step_counter"><?php echo esc_html( $current_step ); ?></span>
							<span class="onboarding_step_name only_desktop"><?php echo esc_html( $step['name'] ); ?></span>

							<?php if ( isset( $step['desc'] ) ) : ?>
								<span class="onboarding_step_desc only_desktop"><?php echo wp_kses_post( $step['desc'] ); ?></span>
							<?php endif; ?>
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
			$description = __( 'Thank you for selecting this plugin for your next WordPress Project. Make sure that your dependency plugin is installed and active. If it is not, we will let you install it right from this page.', 'tws-onboarding' );
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

			$show = ! $this->is_installed;
			$msg  = '';
			if ( is_wp_error( $status ) ) {
				$code = $status->get_error_code();
				$show = 'force_install_execution' === $code ? false : $show;
				$msg  = $status->get_error_message();
			} elseif ( is_string( $status ) ) {
				$msg = $status;
			}

			/**
			 * WPHOOK: Filter -> Default intro information.
			 *
			 * @param array  $args      The content title and desccription.
			 * @param string $prefix    The onboarding prefix.
			 * @param bool   $installed Whether dependency plugin installed or not.
			 * @var array
			 * @since 1.0
			 */
			$intro_args = apply_filters(
				'hzfex_onboarding_intro_default_content',
				array(
					'title'  => $title,
					'desc'   => $description,
					'button' => $button_text,
				),
				$this->prefix,
				$this->is_installed
			);

			$dependency_args = array(
				'slug'        => $this->slug,
				'name'        => $this->name,
				'status'      => $msg, // Can be "installed", "pending" or "WP_Error message".
				'next_step'   => $this->get_next_step_link(),
				'button_text' => $intro_args['button'],
				'show'        => $show,
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
					<?php if ( 'pending' === $status ) : ?>
						<p class="onboarding-dy-title"><?php echo wp_kses_post( $dy_title ); ?></p>
					<?php endif; ?>
					<?php TheWebSolver::get_template( 'dependency.php', $dependency_args, '', $this->config->get_path() . 'templates/' ); ?>
					<?php
				endif;
			else :
				?>
				<!-- Action Buttons -->
				<p id="hz_dyn_btnWrapper" class="hz_dyn_btnWrapper hz_step_actions step onboarding-actions">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-next hz_dyn_btn main_btn hz_btn__prim"><?php echo esc_html( $intro_args['button'] ); ?> →</a>
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
			$msg         = __( 'You messed up the code! Please contact the developer :)', 'tws-onboarding' );
			$noparam     = __( 'The plugin could not be installed due to an invalid slug, filename, or version. Manual installation required.', 'tws-onboarding' );
			$isinstalled = __( 'The plugin is already installed. You are trying to bypass the security. Do not force me to come get you!!!', 'tws-onboarding' );

			// Bail if ajax errors.
			if ( false === check_ajax_referer( $this->prefix . '_install_dep_action', $this->prefix . '_install_dep_key' ) ) {
				exit( wp_kses_post( $msg ) );
			}

			$post = wp_unslash( $_POST );

			$slug      = isset( $post['slug'] ) && ! empty( $post['slug'] ) ? $post['slug'] : 'false';
			$file      = isset( $post['file'] ) && ! empty( $post['file'] ) ? $post['file'] : 'false';
			$version   = isset( $post['version'] ) && ! empty( $post['version'] ) ? $post['version'] : 'false';
			$name      = isset( $post['name'] ) && ! empty( $post['name'] ) ? $post['name'] : $this->name;
			$prefix    = isset( $post['prefix'] ) && ! empty( $post['prefix'] ) ? $post['prefix'] : $this->prefix;
			$installed = isset( $post['installed'] ) && is_bool( $post['installed'] ) ? $post['installed'] : $this->is_installed;

			$validate = array( $slug, $file, $version );

			// If invalid slug, file or version, $validate => false.
			if ( in_array( 'false', $validate, true ) ) {
				$error = new \WP_Error( 'invalid_plugin', $noparam );
				update_option( $prefix . '_onboarding_dependency_status', $error );
				wp_send_json_error( $error, 404 );
				exit( wp_kses_post( $noparam ) );
			}

			// If trying to install it again, stop execution.
			if ( true === $installed ) {
				$error = new \WP_Error( 'force_install_execution', $isinstalled );
				update_option( $prefix . '_onboarding_dependency_status', $error );
				wp_send_json_error( $error, 404 );
				exit( wp_kses_post( $isinstalled ) );
			}

			// Start installation. Suppress feedback.
			ob_start();

			// NOTE: Sometime installation may throw "An unexpected error occurred" WordPress warning. Error messge: (WordPress could not establish a secure connection to WordPress.org. Please contact your server administrator.)
			// Also, plugin activation triggers Ajax in an infinite loop without activation. So, $activate => false.
			$response = TheWebSolver::maybe_install_plugin( $slug, $file, $version, false );

			// Discard feedback.
			ob_end_clean();

			// Update option to reflect installed status if we get dependency plugin name as response.
			if ( is_string( $response ) && $response === $name ) {
				update_option( $prefix . '_onboarding_dependency_status', 'installed' );
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
			 * @var array
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

						// Previous state of the current plugin.
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
					?>

				</fieldset>

				<?php
				// Set the button text accordingly.
				if ( in_array( 'false', $plugins_status, true ) ) :
					$text = __( 'Save & Continue', 'tws-onboarding' );
					?>
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
					<div class="hz_recommended_active_notice hz_flx row center"><span><b><?php echo esc_html( $title ); ?></b> <?php esc_html_e( 'is already active', 'tws-onboarding' ); ?></span></div>
				<?php endif; ?>
			</div>

			<?php
		}

		/**
		 * Sets onboarding final step.
		 *
		 * @since 1.0
		 */
		protected function final_step() {
			$this->reset();

			$dep_option = $this->prefix . '_onboarding_dependency_status';
			$dep_status = get_option( $dep_option );
			if ( isset( $this->reset['dependency_status'] ) && $this->reset['dependency_status'] ) {
				delete_option( $dep_option );
			} else {
				if ( is_wp_error( $dep_status ) ) {
					$code    = $dep_status->get_error_message();
					$message = 'force_install_execution' === $code ? 'installed' : $dep_status->get_error_message();
					update_option( $dep_option, $message );
				}
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
			 * @since 1.0
			 */
			do_action( 'hzfex_onboarding_before_final_step_contents', $this->prefix );

			$title = __( 'Onboarding Wizard Completed Successfully!', 'tws-onboarding' );
			$desc  = __( 'Onboarding wizard is complete. Your plugin is now ready!', 'tws-onboarding' );

			/**
			 * WPHOOK: Filter -> Onboarding ready step contents.
			 *
			 * @param array  $content The onboarding final step contents.
			 * @param string $prefix  The onboarding prefix.
			 * @var array    $content Onboarding ready step content.
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
				<h1><?php echo wp_kses_post( $content['title'] ); ?></h1>
				<p class="onboarding_complete_content"><?php echo wp_kses_post( $content['desc'] ); ?></p>
			</div>

			<?php
			/**
			 * WPHOOK: Action -> Fires after final step contents.
			 *
			 * @param string $prefix The onboarding prefix.
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
			 * @var array
			 * @since 1.0
			 * @example usage
			 * ```
			 * add_filter( 'hzfex_register_onboarding_scripts', 'dependency_scripts', 10, 2 );
			 * function dependency_scripts( array $handles, string $prefix ): array {
			 *  // Check if is our onboarding.
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
			*/
			$script_handles = apply_filters( 'hzfex_register_onboarding_scripts', array( 'jquery', 'hzfex_select2' ), $this->prefix );

			wp_register_script( 'onboarding_script', $this->config->get_url() . 'Assets/onboarding.js', $script_handles, '1.0', false );

			$nonce_key    = $this->prefix . '_install_dep_key';
			$nonce_action = $this->prefix . '_install_dep_action';
			$ajax_action  = $this->prefix . '_silent_plugin_install';

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
						$nonce_key  => wp_create_nonce( $nonce_action ),
						'action'    => $ajax_action,
						'slug'      => $this->slug,
						'file'      => $this->filename ? $this->filename : $this->slug,
						'version'   => $this->version,
						'name'      => $this->name,
						'prefix'    => $this->prefix,
						'installed' => $this->is_installed,
					),
					'recommended' => array(
						'single' => __( 'Plugin', 'tws-onbaording' ),
						'plural' => __( 'Plugins', 'tws-onboarding' ),
						'suffix' => __( 'will be installed and/or activated', 'tws-onboarding' ),
						'select' => __( 'Select one or more plugins above to install/activate', 'tws-onboarding' ),
						'check'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="hz_ob_check"><g class="paths"><path fill="currentColor" d="M504.5 144.42L264.75 385.5 192 312.59l240.11-241a25.49 25.49 0 0 1 36.06-.14l.14.14L504.5 108a25.86 25.86 0 0 1 0 36.42z" class="secondary"></path><path fill="currentColor" d="M264.67 385.59l-54.57 54.87a25.5 25.5 0 0 1-36.06.14l-.14-.14L7.5 273.1a25.84 25.84 0 0 1 0-36.41l36.2-36.41a25.49 25.49 0 0 1 36-.17l.16.17z" class="primary"></path></g></svg>',
					),
					'selectPlh'   => __( 'Select Options', 'tws-onboarding' ),
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
			 * @var array
			 * @since 1.0
			 * @example usage
			 * ```
			 * add_filter( 'hzfex_register_onboarding_styles', 'dependency_styles', 10, 2 );
			 * function dependency_styles( array $handles, string $prefix ): array {
			 *  // Check if is our onboarding.
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
			*/
			$style_handles = apply_filters( 'hzfex_register_onboarding_styles', array( 'hzfex_select2_style', 'googleFont' ), $this->prefix );
			wp_register_style( 'onboarding_style', $this->config->get_url() . 'Assets/onboarding.css', $style_handles, '1.0' );

			/**
			 * WPHOOK: Action -> fires after enqueue onboarding styles and scripts.
			 *
			 * @param array $handles The registered scripts and styles for onboarding wizard.
			 * @param string $prefix The onboarding prefix.
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
			<fieldset class="onboarding-actions step <?php echo esc_attr( $this->step ); ?> hz_flx column center">
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
		 * @since 1.1 Moved onboarding intro page hero image to a template file.
		 */
		protected function introduction_image() {
			TheWebSolver::get_template( 'onboarding-image.php', array(), '', $this->config->get_path() . 'templates/' );
		}
	}
}
