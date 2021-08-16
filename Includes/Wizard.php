<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver WordPress Admin Onboarding Wizard Instantiation.
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

/**
 * Onboarding namespace.
 *
 * @todo MUST REPLACE AND USE OWN NAMESPACE.
 */
namespace My_Plugin\My_Feature;

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
	 * Current config instance.
	 *
	 * @var Config Overriding property on child for IDE/Code Editor support.
	 *
	 * @since 1.1
	 */
	protected $config;

	/**
	 * Resets (deletes) options added during onboarding.
	 * ------------------------------------------------------------------------------
	 * It will not delete options that are saved on child-class onboarding steps.\
	 * It will only delete options saved for onboarding purpose.
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
		$this->slug     = 'query-monitor';
		$this->filename = 'query-monitor'; // Not needed as it is same as slug. Included as an example.
		$this->version  = '3.6.0'; // Not needed if latest to install. Can be: '3.3.3', '2.17.0', '2.6.9' etc (https://plugins.trac.wordpress.org/browser/query-monitor/#tags).
	}

	/**
	 * Sets onboarding HTML head title.
	 *
	 * @todo Change your onboarding title.
	 * @inheritDoc
	 */
	protected function set_title() {
		$this->title = __( 'Thewebsolver &rsaquo; Onboarding', 'tws-onboarding' );
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
			'src'    => $this->config->get_url() . 'Assets/onboarding.svg',
		);
	}

	/**
	 * Onboarding steps.
	 *
	 * @todo Set your own onboarding steps.\
	 *       `Introduction`, `Recommended` and `Ready` steps have action and filter hooks to change the contents.
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
				// Disabling description for this step.
				// 'desc' => __( 'Radio and select dropdown form fields step subtitle displayed in the onboarding steps.', 'tws-onboarding' ), // phpcs:ignore -- Valid Code OK.
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
		$this->config->form->start();

		?>
		<!-- Form Fields -->
		<?php

		// Text input field.
		$this->config->form->add_field(
			'text',
			array(
				'id'          => 'first_ob_field',
				'label'       => 'Text Input',
				'placeholder' => 'Placeholder text',
			)
		);

		// Textarea field.
		$this->config->form->add_field(
			'textarea',
			array(
				'id'    => 'second_ob_field',
				'label' => 'Textarea Input',
				'desc'  => '<div>A short description about the textarea field.</div><div class="option_notice success">This is a success notification.</div>',
			)
		);

		// Checkbox/switch field.
		$this->config->form->add_field(
			'checkbox',
			array(
				'id'    => 'third_ob_field',
				'label' => 'Checkbox Switch',
				'desc'  => '<span>The checkbox HTML structure is designed with modern look with switcher control. </span><span class="alert">This is an alert notification.</span>',
			)
		);

		?>
		<!-- Form Fields end -->
		<?php

		$this->get_step_buttons(); // MUST USE THIS FOR NONCE AND SAVING THIS STEP DATA.

		$this->config->form->end();
	}

	/**
	 * Saves `general` step options.
	 */
	public function text_checkbox_save() {
		$this->validate_save(); // MUST USE THIS FOR NONCE VERIFICATION.

		$this->config->form->save(
			array(
				'first_ob_field'  => 'text',
				'second_ob_field' => 'textarea',
				'third_ob_field'  => 'checkbox',
			)
		);

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Displays `front` step Options.
	 */
	public function radio_select_form_view() {
		$this->config->form->start();

		?>
		<!-- Form Fields -->
		<?php

		// Radio buttons.
		$this->config->form->add_field(
			'radio',
			array(
				'id'      => 'fourth_ob_field',
				'label'   => 'Dynamic Radio',
				'desc'    => '<p>The radio field description. Use can use any valid HTML tag to style it as needed.</p>',
				'options' => array(
					'first'  => '<p>Radio input first option</p><div class="desc"><b>First Option</b> is just for the demo purpose. This is just a long description explaining about the first option in this advanced radio field.</div>',
					'second' => '<p>Radio input second option</p><div class="desc"><b>Second Option</b> This can also be any valid HTML tag such as adding images.</div>',
				),
				'class'   => 'widefat', // make radio options 100% width.
			)
		);

		// Select options.
		$this->config->form->add_field(
			'select',
			array(
				'id'      => 'fifth_ob_field',
				'label'   => 'Dynamic Select',
				'desc'    => '<p>The select field will be converted to advanced select field using select2 library.</p>',
				'options' => array(
					'first'  => 'First Option',
					'second' => 'Second Option',
					'third'  => 'Third Option',
					'fourth' => 'Fourth Option',
				),
			)
		);

		?>
		<!-- Form Fields end -->
		<?php

		$this->get_step_buttons( true ); // MUST USE THIS FOR NONCE AND SAVING THIS STEP DATA.

		$this->config->form->end();
	}

	/**
	 * Saves `front` step Options.
	 */
	public function radio_select_form_save() {
		$this->validate_save(); // MUST USE THIS FOR NONCE VERIFICATION.

		$this->config->form->save(
			array(
				'fourth_ob_field' => 'radio',
				'fifth_ob_field'  => 'select',
			)
		);

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}
}
