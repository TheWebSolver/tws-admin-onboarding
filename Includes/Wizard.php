<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver Onboarding Wizard Initialization.
 * Boilerplate child-class to extend onboarding wizard class.
 *
 * @todo Set the wizard namespace.
 * @todo Make changes where applicable.
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

use TheWebSolver\Core\Admin\Onboarding\Wizard;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Onboarding Wizard configuration.
 *
 * {@see @method Config::onboarding()}
 *
 * This extends the main Wizard class.
 * Use this as a boilerplate for creating own onboarding wizard.
 */
class Onboarding_Wizard extends Wizard {
	/**
	 * Onboarding config instance.
	 *
	 * @var object
	 */
	private $config;

	/**
	 * Sets an instance of Config in this namespace.
	 *
	 * @param object $instance The onboarding config instance.
	 */
	public function set_config( $instance ) {
		$this->config = $instance;
	}

	/**
	 * Gets the wizard config instance.
	 *
	 * @return Config
	 */
	private function config() {
		return $this->config;
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
	 * @todo Set option to be deleted to `true`.
	 * @inheritDoc
	 */
	protected function reset() {
		// Every option is set to false here so nothing gets deleted.
		// true will delete option, false will not.
		$this->reset = array(
			'dependency_name'            => false,
			'dependency_status'          => false,
			'recommended_status'         => false,
			'recommended_checked_status' => false,
		);
	}

	/**
	 * Sets dependency plugin args (use this if need to install required plugin at first step).

	 * @example usage
	 * ```
	 * namespace My_Plugin\My_Feature;
	 * use TheWebSolver\Core\Admin\Onboarding\Wizard;
	 *
	 * // Lets assume our child-class is `Onboarding_Wizard` in above namespace.
	 * class Onboarding_Wizard extends Wizard {
	 *  protected function set_dependency() {
	 *   // Lets make Advanced Custom Fields plugin as a required dependency plugin.
	 *   $this->slug     = 'advanced-custom-fields';
	 *   $this->filename = 'acf'; // Filename different from slug, so include.
	 *   $this->version  = '5.9.4'; // Not needed if latest to install. Can be: '5.9.0', '5.8.8' etc.
	 *  }
	 * }
	 * ```
	 * @todo Set your own dependency plugin data. If not needed, delete this method.
	 * @inheritDoc
	 */
	protected function set_dependency() {
		$this->slug     = 'woocommerce';
		$this->filename = 'woocommerce'; // Not needed as it is same as slug. Included as an example.
		$this->version  = '4.0.0'; // Not needed if latest to install. Can be: '4.9.2', '4.5.0', '4.0.0' etc.
	}

	/**
	 * Sets onboarding prefix.
	 *
	 * @inheritDoc
	 */
	protected function set_prefix() {
		$this->prefix = $this->config()->get_prefix();
	}

	/**
	 * Sets onboarding page slug.
	 *
	 * @inheritDoc
	 */
	protected function set_page() {
		$this->page = $this->config()->get_page();
	}

	/**
	 * Sets onboarding root URL relative to current plugin's directory.
	 *
	 * @inheritDoc
	 */
	protected function set_url() {
		$this->url = trailingslashit( $this->config()->get_url() );
	}

	/**
	 * Sets onboarding root path relative to current plugin's directory.
	 *
	 * @inheritDoc
	 */
	protected function set_path() {
		$this->path = $this->config()->get_path();
	}

	/**
	 * Sets onboarding HTML head title.
	 *
	 * @todo Change your onboarding title.
	 * @inheritDoc
	 */
	protected function set_title() {
		$this->title = __( 'MyPlugin Onboarding', 'tws-onboarding' );
	}

	/**
	 * Sets user capability to run onboarding wizard.
	 *
	 * @inheritDoc
	 */
	protected function set_capability() {
		$this->capability = $this->config()->get_capability();
	}

	/**
	 * Sets onboarding logo.
	 *
	 * @todo Set your own onboarding logo args.
	 * @inheritDoc
	 */
	protected function set_logo() {
		$this->logo = array(
			'href'   => get_site_url( get_current_blog_id() ),
			'alt'    => 'The Web Solver Onboarding',
			'width'  => '135px',
			'height' => 'auto',
			'src'    => $this->url . 'Assets/onboarding.svg',
		);
	}

	/**
	 * Onboarding steps.
	 *
	 * @todo Set your own onboarding steps.
	 *       `Introduction`, `Recommended` and `Ready` steps are default will be handled automatically.
	 *       These default steps have filters to change the contents.
	 *       and those filters will be displayed on respective step page.
	 * @inheritDoc
	 */
	protected function set_steps() {
		$steps = array(
			'general' => array(
				'name' => __( 'Text/Checkbox Fields', 'tws-onboarding' ),
				'desc' => __( 'Text, textarea and checkbox input fields step subtitle displayed in the onboarding steps.', 'tws-onboarding' ),
				'view' => array( $this, 'text_checkbox_view' ),
				'save' => array( $this, 'text_checkbox_save' ),
			),
			'front'   => array(
				'name' => __( 'Radio/Select Fields', 'tws-onboarding' ),
				'desc' => __( 'Radio and select dropdown form fields step subtitle displayed in the onboarding steps.', 'tws-onboarding' ),
				'view' => array( $this, 'radio_select_form_view' ),
				'save' => array( $this, 'radio_select_form_save' ),
			),
		);

		return $steps;
	}

	/**
	 * Set the recommended plugins.
	 *
	 * @todo Manage recommended plugins. Each plugin will be installed and activated on recommended step.
	 *       There will be enable/disbale option whether or not to intall the recommended plugin.
	 *       As an example, 5 plugins as set as recommended plugins.
	 *       If don't have any recommended plugin, delete this method.
	 * @inheritDoc
	 */
	protected function set_recommended_plugins() {
		$plugins = array(
			array(
				'slug'  => 'show-hooks',
				'title' => __( 'Show Hooks', 'tws-onboarding' ),
				'desc'  => __( 'A sequential and visual representation of WordPess action and filter hooks.', 'tws-onboarding' ),
				'logo'  => 'https://ps.w.org/show-hooks/assets/icon-256x256.png?rev=2327503',
				'alt'   => __( 'Show Hooks logo', 'tws-onboarding' ),
			),
			array(
				'slug'  => 'advanced-custom-fields',
				'file'  => 'acf',
				'title' => __( 'Advanced Custom Fields', 'tws-onboarding' ),
				'desc'  => __( 'Use the Advanced Custom Fields plugin to take full control of your WordPress edit screens & custom field data.', 'tws-onboarding' ),
				'logo'  => 'https://ps.w.org/advanced-custom-fields/assets/icon-256x256.png?rev=1082746',
				'alt'   => __( 'ACF logo', 'tws-onboarding' ),
			),
			array(
				'slug'  => 'query-monitor',
				'file'  => 'query-monitor',
				'title' => __( 'Query Monitor', 'tws-onboarding' ),
				'desc'  => __( 'The Developer Tools Panel for WordPress.', 'tws-onboarding' ),
				'logo'  => 'https://ps.w.org/query-monitor/assets/icon-256x256.png?rev=2301273',
				'alt'   => __( 'Query monitor logo', 'tws-onboarding' ),
			),
			array(
				'slug'  => 'ultimate-member',
				'title' => __( 'Ultimate Member', 'tws-onboarding' ),
				'desc'  => __( 'Ultimate Member is a free user profile WordPress plugin that makes it easy to create powerful online communities and membership sites with WordPress.', 'tws-onboarding' ),
				'logo'  => 'https://ps.w.org/ultimate-member/assets/icon-256x256.png?rev=2143339',
				'alt'   => __( 'Ultimate Member logo', 'tws-onboarding' ),
			),
			array(
				'slug'  => 'wp-reset',
				'title' => __( 'WP Reset', 'tws-onboarding' ),
				'desc'  => __( 'WP Reset quickly resets the site’s database to the default installation values without modifying any files.', 'tws-onboarding' ),
				'logo'  => 'https://ps.w.org/wp-reset/assets/icon-256x256.png?rev=1906468',
				'alt'   => __( 'WP Reset logo', 'tws-onboarding' ),
			),
		);

		$this->recommended = $plugins;
	}

	/**
	 * Displays `general` step options.
	 */
	public function text_checkbox_view() {
		$text     = get_option( 'myprefix_simple_input_value', 'Example text default value' );
		$textarea = get_option( 'myprefix_textarea_value', '' );
		$checkbox = get_option( 'myprefix_checkbox_value', 'off' );
		?>

		<form method="POST">
			<!-- contents -->
			<fieldset>
				<label for="simple_input"><p><?php esc_html_e( 'Text Input', 'tws-onboarding' ); ?></p>
					<input id="simple_input" type="text" name="simple_input" value="<?php echo esc_attr( $text ); ?>">
				</label>
			</fieldset>
			<fieldset>
				<label for="textarea_input"><p><?php esc_html_e( 'Textarea Input', 'tws-onboarding' ); ?></p>
					<textarea id="textarea_input" name="textarea_input" rows="10" cols="50" placeholder="<?php esc_attr_e( 'Example placeholder&#13;Another feature in new line&#13;Last feature in new line&#13;and so on....', 'tws-onboarding' ); ?>"><?php echo esc_html( $textarea ); ?></textarea>
				</label>
			</fieldset>
			<fieldset class="hz_control_field">
				<p class="hz_switcher_control">
					<label for="checkbox_field">
						<span class="hz_switcher_label">
							<?php esc_html_e( 'Checkbox Switch', 'tws-onboarding' ); ?>
							<span class="desc"><?php esc_html_e( 'Use the same HTML elements and classes used for this checkbox input field in order for switcher toggle control to work. If structure is different, then toggle button will not be created and default checkbox will be displayed.', 'tws-onboarding' ); ?></span>
							<span class="option_notice alert desc"><?php esc_html_e( 'Alert: switcher control won\'t work if not used this same elements.', 'tws-onboarding' ); ?></span>
						</span>
						<input type="checkbox" class="hz_checkbox_input" id="checkbox_field" name="checkbox_field" class="hz_checkbox_input" data-control="switch" <?php checked( $checkbox, 'on', true ); ?>>
						<span class="hz_switcher"></span>
					</label>
				</p>
			</fieldset>
			<!-- contents end -->
			<?php $this->get_step_buttons(); // MUST USE THIS FOR NONCE AND SAVING THIS STEP DATA. ?>
		</form>
		<?php
	}

	/**
	 * Saves `general` step options.
	 */
	public function text_checkbox_save() {
		$this->validate_save(); // MUST USE THIS FOR NONCE VERIFICATION.

		$options = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification

		// Prepare options value to save.
		$text     = ! empty( $options['simple_input'] ) ? sanitize_text_field( $options['simple_input'] ) : '';
		$textarea = ! empty( $options['textarea_input'] ) ? sanitize_textarea_field( $options['textarea_input'] ) : '';
		$checkbox = ! empty( $options['checkbox_field'] ) ? sanitize_text_field( $options['checkbox_field'] ) : 'off';

		update_option( 'myprefix_simple_input_value', $text );
		update_option( 'myprefix_textarea_value', $textarea );
		update_option( 'myprefix_checkbox_value', $checkbox );

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Displays `front` step Options.
	 */
	public function radio_select_form_view() {
		$radio  = get_option( 'myprefix_radio_input', 'second_option' );
		$select = get_option( 'myprefix_select_dropdown', 'third_option' );
		?>

		<form method="POST">
			<!-- contents -->
			<fieldset class="hz_control_field hz_radio_field">
				<label for="radio_input"><?php esc_html_e( 'Radio Input', 'tws-onboarding' ); ?></label>
				<p class="desc"><?php esc_html_e( 'Use the same HTML elements and classes used for this radio input field in order for card toggle control to work.', 'tws-onboarding' ); ?></p>
				<span class="option_notice alert desc"><?php esc_html_e( 'Waring: If same structure is not used, the card control won\'t work.', 'tws-onboarding' ); ?></span>
				<ul class="hz_card_control">
					<li class="hz_card_control_wrapper">
						<label for="radio_input_first" class="hz_card_control">
							<input id="radio_input_first" type="radio" name="radio_input" class="hz_card_control" data-control="card" value="first_option" <?php checked( $radio, 'first_option' ); ?>>
							<div class="hz_card_info hz_flx row center">
								<div class="radio_option_content">
									<p><?php esc_html_e( 'Radio input first option', 'tws-onboarding' ); ?></p>
									<p class="radio_subtitle">
										<?php
										echo wp_kses(
											sprintf(
												__( '<b>First Option</b> is just for the demo purpose. This is just a long description explaining about the first option in this advanced radio field', 'tws-onboarding' ),
											),
											array( 'b' => array() )
										);
										?>
									</p>
								</div>
								<div class="radio_option_image">
									<img src="" alt="">
								</div>
							</div>
						</label>
					</li>
					<li class="hz_card_control_wrapper">
						<label for="radio_input_second" class="hz_card_control">
							<input id="radio_input_second" type="radio" name="radio_input" class="hz_card_control" data-control="card" value="second_option" <?php checked( $radio, 'second_option' ); ?>>
							<div class="hz_card_info hz_flx row center">
								<div class="radio_option_content">
									<p><?php esc_html_e( 'Radio input second option', 'tws-onboarding' ); ?></p>
									<p class="radio_subtitle">
										<?php
										echo wp_kses(
											sprintf(
												__( '<b>Second Option</b> is just for the demo purpose. This is just a long description explaining about the second option in this advanced radio field', 'tws-onboarding' ),
											),
											array( 'b' => array() )
										);
										?>
									</p>
								</div>
								<div class="radio_option_image">
									<img src="" alt="">
								</div>
							</div>
						</label>
					</li>
				</ul>
			</fieldset>
			<fieldset class="hz_select_control hz_select_control_wrapper">
				<label for="select_dropdown"><?php esc_html_e( 'Advanced select field', 'tws-onboarding' ); ?></label>
				<p class="desc"><?php esc_html_e( 'Use the same HTML elements and classes used for this select dropdown field in order for select2 to work.', 'tws-onboarding' ); ?></p>
				<select id="select_dropdown" class="hz_select hz_select_control widefat" name="select_dropdown">
					<option value=""></option>
					<option value="first_option" <?php selected( $select, 'first_option' ); ?>><?php esc_attr_e( 'First Option', 'tws-onboarding' ); ?></option>
					<option value="second_option" <?php selected( $select, 'second_option' ); ?>><?php esc_attr_e( 'Second Option', 'tws-onboarding' ); ?></option>
					<option value="third_option" <?php selected( $select, 'third_option' ); ?>><?php esc_attr_e( 'Third Option', 'tws-onboarding' ); ?></option>
					<option value="forth_option" <?php selected( $select, 'forth_option' ); ?>><?php esc_attr_e( 'Forth Option', 'tws-onboarding' ); ?></option>
				</select>
			</fieldset>
			<!-- contents end -->

			<?php $this->get_step_buttons( true ); // MUST USE THIS FOR NONCE AND SAVING THIS STEP DATA. ?>
		</form>
		<?php
	}

	/**
	 * Saves `front` step Options.
	 */
	public function radio_select_form_save() {
		$this->validate_save(); // MUST USE THIS FOR NONCE VERIFICATION.

		$options = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification

		// Prepare options value to save.
		$radio  = ! empty( $options['radio_input'] ) ? sanitize_text_field( $options['radio_input'] ) : 'first_option';
		$select = ! empty( $options['select_dropdown'] ) ? sanitize_text_field( $options['select_dropdown'] ) : 'first_option';

		update_option( 'myprefix_radio_input', $radio );
		update_option( 'myprefix_select_dropdown', $select );

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}
}
